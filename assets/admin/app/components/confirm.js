/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);
const ReactDOM = preactCompat;

export const confirm = (config = {}) => {
    let defaultConfig = {
        title: '',
        message: '',
        okText: 'Ok',
        cancelText: 'Cancel'
    };
    config = Object.assign(defaultConfig, config);

    let modalWrapper,
    modalConfirm,
    modal;

    const onApprove = (e) => {
        e.preventDefault();
        if (config.hasOwnProperty('onApprove')) {
            if (typeof config.onApprove === 'function') {
                modal.hide();
                config.onApprove(true);
            }
        }
    };

    const onCancel = (e) => {
        e.preventDefault();
        if (config.hasOwnProperty('onCancel')) {
            if (typeof config.onCancel === 'function') {
                modal.hide();
                config.onCancel(false);
            }
        }
    };

    let model = html`
        <div class="modal fade" id="modalConfirm" tabindex="-1" aria-labelledby="modalConfirmLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalConfirmLabel">${config.title}</h5>
                        <button type="button" class="btn-close" onclick=${(e) => onCancel(e)} aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${config.message}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-${config.cancelColor ? config.cancelColor : 'default'}" onclick=${(e) => onCancel(e)}>${config.cancelText}</button>
                        <button type="button" class="btn btn-${config.okColor ? config.okColor : 'primary'}" onclick=${(e) => onApprove(e)}>${config.okText}</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', '<div id="modal_confirm_wrapper"></div>');
    modalWrapper = document.getElementById('modal_confirm_wrapper');
    ReactDOM.render(model, modalWrapper);

    modalConfirm = document.getElementById('modalConfirm');
    modal = new bootstrap.Modal(modalConfirm, {
        backdrop: 'static',
        keyboard: false,
    });
    modal.show();

    modalConfirm.addEventListener('hidden.bs.modal', () => {
        modalWrapper.remove();
    });
};