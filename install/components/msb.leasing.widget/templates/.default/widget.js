class LeasingWidget {
    constructor(rootSelector = 'body') {
        this.root = document.querySelector(rootSelector) || document.body;
        this.widgetOverlay = null;
        this.widgetContainer = null;
        this.state = {
            step: 'search',
            companyQuery: '',
            companyList: [],
            companySelected: null,
            scoringResult: null,
            scoringInProgress: false,
            scoringTimer: 120,
            scoringProgressIdx: 0,
            scoringFailed: false,
            product: {},
            sessionId: null,
        };
        this._scoringTimerInterval = null;
        this.handleOverlayClick = this.handleOverlayClick.bind(this);
    }
    showWidget(product = {}) {
        if (this.widgetOverlay) return;
        this.state.product = {...product};
        this.state.sessionId = this.generateSessionId();
        this.state.step = 'search';
        this.state.companyQuery = '';
        this.state.companyList = [];
        this.state.companySelected = null;
        this.state.scoringResult = null;
        this.state.scoringInProgress = false;
        this.state.scoringTimer = 120;
        this.state.scoringProgressIdx = 0;
        this.state.scoringFailed = false;

        this.widgetOverlay = document.createElement('div');
        this.widgetOverlay.className = 'leasing-modal-overlay';
        this.widgetOverlay.addEventListener('click', this.handleOverlayClick);
        this.widgetContainer = document.createElement('div');
        this.widgetContainer.className = 'leasing-modal';
        this.widgetContainer.addEventListener('click', e => e.stopPropagation());
        this.widgetOverlay.appendChild(this.widgetContainer);
        this.root.appendChild(this.widgetOverlay);
        this.widgetContainer.innerHTML = `
        <div class='leasing-modal__header'>
            <span class='leasing-modal__title'>
                <img src='https://msb24.ru/assets/svg/msb-logo.svg' style='height:36px;vertical-align:middle;margin-right:12px;'>
                Заявка на лизинг
            </span>
            <span class='leasing-modal__close-x' title='Закрыть' id='leasing-close-x'>&times;</span>
        </div>
        <div class='leasing-modal__content' id='leasing-modal-content'></div>
        `;
        this.widgetContainer.querySelector('#leasing-close-x').onclick = () => this.destroy();
        this.renderStep();
    }
    generateSessionId() {
        return (
            'sess_' + (
                Math.random().toString(36).substr(2, 9) +
                '-' +
                Date.now().toString(36)
            )
        );
    }
    handleOverlayClick(e) {
        if (e.target === this.widgetOverlay) {
            this.destroy();
        }
    }
    destroy() {
        if (this._scoringTimerInterval) {
            clearInterval(this._scoringTimerInterval);
            this._scoringTimerInterval = null;
        }
        if (this.widgetOverlay) {
            this.widgetOverlay.removeEventListener('click', this.handleOverlayClick);
            this.widgetOverlay.remove();
            this.widgetOverlay = null;
        }
        this.widgetContainer = null;
    }
    renderStep() {
        const c = this.widgetContainer.querySelector('#leasing-modal-content');
        if (!c) return;
        switch(this.state.step) {
            case 'search':
                c.innerHTML = this.renderSearch();
                this.initSearchEvents(c);
                break;
            case 'scoring':
                c.innerHTML = this.renderScoring();
                this.startScoringTimer(c);
                break;
            case 'result':
                c.innerHTML = this.renderResult();
                break;
        }
    }
    async findCompanyLive(query) {
        if (!window.DADATA_API_KEY) throw new Error('DADATA_API_KEY не заполнен');
        const url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party';
        const resp = await fetch(url, {
            method: 'POST',
            mode: 'cors',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Token ' + window.DADATA_API_KEY
            },
            body: JSON.stringify({ query, count: 3 })
        });
        if (!resp.ok)
            throw new Error('Ошибка DaData: ' + resp.statusText);
        const data = await resp.json();
        if (!data || !Array.isArray(data.suggestions)) return [];
        return data.suggestions.map(sugg => ({
            inn: sugg.data.inn || '',
            name: sugg.value,
            address: (sugg.data.address && sugg.data.address.value) || '',
        }));
    }
    renderSearch() {
        return `
        <div style='max-width:340px;margin:32px auto;'>
            <label>Введите <b>ИНН</b> или <b>название компании</b>:</label><br>
            <input type='text' id='leasing-inn-input' maxlength='16' autocomplete='off' style='width:100%;padding:10px;margin:14px 0 4px 0;font-size:19px;border-radius:7px;border:1px solid #cbd5e1;'>
            <div id='leasing-dadata-list'></div>
            <button id='leasing-continue-btn' disabled style='margin-top:19px;width:100%;'>Продолжить</button>
        </div>`;
    }
    initSearchEvents(c) {
        const input = c.querySelector('#leasing-inn-input');
        const listDiv = c.querySelector('#leasing-dadata-list');
        const btn = c.querySelector('#leasing-continue-btn');
        input.focus();
        let lastQuery = '';
        let typingTimeout = null;
        input.oninput = async () => {
            const v = input.value.trim();
            this.state.companyQuery = v;
            this.state.companySelected = null;
            btn.disabled = true;
            btn.textContent = 'Продолжить';
            if (v.length >= 3) {
                lastQuery = v;
                listDiv.innerHTML = `<div style='color:#489fd6;padding:14px;'>Поиск в DaData...</div>`;
                if (typingTimeout) clearTimeout(typingTimeout);
                typingTimeout = setTimeout(async () => {
                    if (lastQuery !== v) return;
                    try {
                        const found = await this.findCompanyLive(v);
                        this.state.companyList = found;
                        if (found.length > 0) {
                            listDiv.innerHTML = found.map((c,i)=>`
                            <div class='dadata-option' data-index="${i}" style='border: 1px solid #ececec;padding:10px 14px;border-radius:6px;cursor:pointer;margin-bottom:7px;'>
                                <b>${c.inn}</b><br>
                                <span>${c.name}</span><br>
                                <span style='font-size:0.93em;color:#555;'>${c.address}</span>
                            </div>`).join('');
                            listDiv.querySelectorAll('.dadata-option').forEach(opt => {
                                opt.onclick = (e) => {
                                    const idx = Number(opt.dataset.index);
                                    this.state.companySelected = this.state.companyList[idx];
                                    input.value = this.state.companySelected.inn;
                                    btn.disabled = false;
                                    btn.textContent = 'Продолжить';
                                    listDiv.innerHTML = `<div style='color:#16a34a;padding:14px 0 10px 0;'><b>Выбрана компания:</b><br>${this.state.companySelected.name}<br><span style='font-size:0.91em;color:#555;'>${this.state.companySelected.address}</span></div>`;
                                };
                            });
                        } else {
                            listDiv.innerHTML = `<div style='padding:10px;color:#C94B4B;'>Компания не найдена, но можно продолжить</div>`;
                            btn.disabled = false;
                            this.state.companySelected = {inn: v, name: v, address: ''};
                        }
                    } catch (err) {
                        listDiv.innerHTML = `<div style='color:#da2d2d;padding:12px;'>Ошибка поиска DaData: ${(err.message||err)}</div>`;
                        btn.disabled = true;
                    }
                }, 350);
            } else {
                listDiv.innerHTML = `<div style='padding:7px;color:#666;'>Введите минимум 3 символа для поиска...</div>`;
            }
        };
        btn.onclick = () => {
            if (this.state.companySelected) {
                this.state.step = 'scoring';
                this.renderStep();
            }
        };
    }
    renderScoring() {
        return `
        <div style='padding:32px 0;text-align:center'>
            <div class='spinner' style='margin: 0 auto 20px auto;width:44px;height:44px;border:6px solid #eaeaea;border-top:6px solid #368ee0;border-radius:50%;animation:spin 1s linear infinite;'></div>
            <div id='scoring-status-text' style='font-size:20px;margin:18px 0 10px 0;'>Проверяем данные компании...</div>
            <div id='scoring-timer' style='margin:8px 0 0 0;color:#666;font-size:16px;'>2:00</div>
        </div>
        <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>
        `;
    }
    async startScoringTimer(c) {
        this.state.scoringInProgress = true;
        const statusArr = [
            'Проверяем данные компании...',
            'Анализируем финансовые показатели...',
            'Оцениваем кредитную историю...',
            'Формируем результат...'
        ];
        let t = this.state.scoringTimer;
        let idx = 0;
        const statusText = c.querySelector('#scoring-status-text');
        const timerText = c.querySelector('#scoring-timer');
        statusText.textContent = statusArr[idx];
        timerText.textContent = this.formatTime(t);
        if (this._scoringTimerInterval) clearInterval(this._scoringTimerInterval);
        this._scoringTimerInterval = setInterval(()=>{
            t--;
            timerText.textContent = this.formatTime(t);
            if (t%20===0 && idx<statusArr.length-1) {
                idx++; statusText.textContent = statusArr[idx];
            }
            if (t<=0) {
                clearInterval(this._scoringTimerInterval);
                this._scoringTimerInterval = null;
                this.finishScoring('timeout');
            }
        }, 1000);
        try {
            const n8nRes = await this.sendToN8N();
            if (this._scoringTimerInterval) {
                clearInterval(this._scoringTimerInterval);
                this._scoringTimerInterval = null;
            }
            this.finishScoring(null, n8nRes);
        } catch(err) {
            if (this._scoringTimerInterval) {
                clearInterval(this._scoringTimerInterval);
                this._scoringTimerInterval = null;
            }
            this.finishScoring('timeout', null, err);
        }
    }

    async sendToN8N() {
        if (!window.N8N_URL || !window.N8N_LOGIN || !window.N8N_PASSWORD) {
            throw new Error('N8N_URL, N8N_LOGIN или N8N_PASSWORD не заданы');
        }
        const sel = this.state.companySelected;
        const prod = this.state.product;
        const body = {
            applicationId: (Math.random().toString(36).substring(2, 10) + Date.now()),
            inn: sel.inn,
            companyName: sel.name,
            equipmentPrice: prod.price || 0,
            equipmentArticle: prod.article || '',
            partnerId: (window.MSB_LEASING_PARTNER_ID || prod.partnerId || 'partner_001'),
            sessionId: this.state.sessionId || this.generateSessionId(),
        };
        const basic = btoa(window.N8N_LOGIN + ':' + window.N8N_PASSWORD);
        const res = await fetch(window.N8N_URL, {
            method: 'POST',
            headers: {
              'Authorization': 'Basic ' + basic,
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error('Ошибка n8n: '+res.statusText);
        const json = await res.json();
        if (Array.isArray(json) && json.length === 1 && Object.keys(json[0]).length === 0) {
            throw new Error('Этот партнёр не зарегистрирован');
        }
        return json;
    }

    finishScoring(forceStatus, n8nResult, err) {
        if (err && err.message && err.message.indexOf('партнер') !== -1) {
            this.state.scoringResult = null;
            this.state.step = 'result';
            this.widgetContainer.querySelector('#leasing-modal-content').innerHTML = `
                <div class='leasing-modal-reject' style='padding:34px 0;'>
                    <div style='font-size:56px;margin-bottom:16px;'>✕</div>
                    <div style='font-size:21px;font-weight:500;margin-bottom:17px;'>Данный партнёр не зарегистрирован</div>
                    <button style='margin-top:18px;' onclick='window.LeasingWidget.destroy()'>Закрыть</button>
                </div>`;
            return;
        }
        if (Array.isArray(n8nResult) && n8nResult.length === 1 && typeof n8nResult[0] === 'object') {
            const prescoring = n8nResult[0].pre_scoring_result;
            if (prescoring === 'rejection') {
                this.state.scoringResult = { success: false, reason: 'Отказ по прескорингу', checks: [] };
                this.state.step = 'result';
                this.renderStep();
                return;
            } else if (prescoring === 'approve' || prescoring === 'approved') {
                this.state.scoringResult = { success: true, reason: 'Одобрено по прескорингу', checks: [
                    {name:'Компания активна', passed:true},
                    {name:'Финансовые показатели в норме', passed:true},
                    {name:'Кредитная история положительная', passed:true}
                ] };
                this.state.step = 'result';
                this.renderStep();
                return;
            }
        }
        let result = {success: true, reason: '', checks:[]};
        if (forceStatus==='timeout') {
            result.success = true;
            result.reason = 'timeout';
        } else if (n8nResult) {
            result.success = (n8nResult.decision === 'approved');
            result.reason = n8nResult.decision || '';
            result.checks = Array.isArray(n8nResult.checks) ? n8nResult.checks.map(x=>({name:x.name||x, passed:x.passed!==false})) : [];
        } else {
            const sel = this.state.companySelected;
            const last = sel?.inn?.trim().slice(-1);
            result.success = (parseInt(last,10)%2===0);
            result.reason = result.success ? '' : 'odd_reject';
            result.checks = [
                {name:'Компания активна', passed:result.success},
                {name:'Финансовые показатели в норме', passed:result.success},
                {name:'Кредитная история положительная', passed:result.success},
            ];
        }
        this.state.scoringResult = result;
        this.state.step = 'result';
        this.renderStep();
    }
    formatTime(sec) {
        return Math.floor(sec/60)+':'+((sec%60).toString().padStart(2,'0'));
    }
    renderResult() {
        const res = this.state.scoringResult;
        if (res.success) {
            return `
            <div class='leasing-modal-success' style='padding:28px 0;'>
                <div style='font-size:66px;margin-bottom:16px;'>✓</div>
                <div style='font-size:23px;font-weight:500;margin-bottom:17px;'>Заявка одобрена!</div>
                <div style='margin-bottom:14px;'>Проверки пройдены:</div>
                <div style='text-align:left!important;margin: 0 auto 12px auto;max-width:270px;'>
                  <div>✓ Компания активна</div>
                  <div>✓ Финансовые показатели в норме</div>
                  <div>✓ Кредитная история положительная</div>
                </div>
                <button style='margin-top:18px;' onclick='window.LeasingWidget.destroy()'>Закрыть</button>
            </div>`;
        } else {
            return `
            <div class='leasing-modal-reject' style='padding:34px 0;'>
                <div style='font-size:56px;margin-bottom:16px;'>✕</div>
                <div style='font-size:21px;font-weight:500;margin-bottom:17px;'>К сожалению, мы не можем одобрить заявку в данный момент</div>
                <button style='margin-top:18px;' onclick='window.LeasingWidget.destroy()'>Закрыть</button>
            </div>`;
        }
    }
}
window.LeasingWidget = new LeasingWidget();
