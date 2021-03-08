/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

import { timeAgo } from '../../utils.js';
import { Ocurrency } from './ocurrency.js';
import { confirm } from '../../components/confirm.js';

const copyToClipboard = (id) => {
    var item = document.getElementById(id);

    document.body.insertAdjacentHTML('beforeend', `
    <div id="${'toast-wrapper-' + id}" class="position-fixed top-0 end-0 p-3" style="z-index: 5">
        <div id="${'toast-' + id}" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="d-flex">
                    <div class="toast-body">
                        Copied to clipboatd!
                        <textarea id="toCopy" style="display:none;">${item.innerHTML}</textarea>
                    </div>
                </div>
            </div>
        </div>
    `);

    let toast = document.getElementById('toast-' + id);
    let toastWrapper = document.getElementById('toast-wrapper-' + id);

    var copyText = document.getElementById('toCopy');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    let toastBlock = new bootstrap.Toast(toast, {
        delay: 1500,
        autohide: true
    });
    toastBlock.show();
    toast.addEventListener('hidden.bs.toast', function () {
        toastWrapper.remove();
    });
}

const deleteItem = async (e, id) => {
    e.preventDefault();
    confirm({
        okText: 'Delete item',
        okColor: 'danger',
        cancelText: 'Cancel',
        cancelColor: 'secondary',
        title: `Delete log ${id}`,
        message: `Do you want delete this issue? This action can not be undone.`,
        onApprove: async () => {
            document.getElementById(`log-item-${_id}`).classList.add('disabled');
            let deleted = await WugsApi.fetch({ method: 'GET', path: `log/${id}` });
            if (deleted) {
                dispatch({
                    type: 'set_messages', payload: [{
                        type: 'success',
                        message: 'Item removed successfully!'
                    }]
                });
                dispatch({ type: 'remove_log', payload: id });
            } else {
                dispatch({ type: 'set_messages', payload: [{
                    type: 'error',
                    message: 'Item not removed. Try again!'
                }] });
                document.getElementById(`log-item-${_id}`).classList.remove('disabled');
            }
        },
        onCancel: () => {
            console.log('action cancelled')
        }
    })
};

const CodeType = ({source}) => {
    if (source === 'js') return html`<div><span class="badge bg-warning text-dark">JS</span> <span class="text-secondary">Javascript</span></div>`;
    if (source === 'php') return html`<div><span class="badge bg-danger text-dark">PHP</span><span class="text-secondary">PHP</span></div>`;
    return;
};

const LogTitle = ({msg}) => {
    let firstText = msg.split(':')[0];
    let secondarytext = msg.replace(firstText, '');
    return html`<span class="text-primary sb-1"><b>${firstText}</b><span class="text-dark">${secondarytext}</span></span>`;
}

export const LogItem = ({ item, dispatch }) => {
    const { _id, msg, count, source, type, url, activity, messages, created_at, updated_at } = item;

    return html`
        <div id="log-item-${_id}" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <${LogTitle} ...${{ msg }} />
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm" title="Delete item" data-bs-toggle="tooltip" data-bs-placement="top" onclick=${(e) => deleteItem(e, _id)}>
                    <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex w-100 justify-content-between">
                <div class="sb-1"><small class="text-secondary sb-1">${url}</small></div>
                <${Ocurrency} log=${item} />
            </div>
            <div class="mb-2"/>
            <p class="mb-1">
                
            </p>
            <div class="collapse" id="collapse-log-item-${_id}">            
                <div class="card">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a class="text-secondary" role="button" onclick=${() => copyToClipboard(`log-item-json-${_id}`)} style="float:right;text-decoration:none;">
                                <i class="bi bi-code-square text-dark"></i> Copy
                            </a>
                        </li>
                        <li class="list-group-item">
                            <pre id="log-item-json-${_id}" class="pre-scrollable" style="max-height: 250px;white-space: pre-wrap;">
                                ${JSON.stringify(item, null, 2)}
                            </pre>
                        </li>
                    </ul>                    
                </div>
            </div>
            <div class="collapse" id="collapse-log-item-activity-${_id}">            
                <div class="card">
                    <ul class="list-group list-group-flush">
                        ${activity.map(aItem => {
                            return html`
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="text-dark"><i class="bi bi-bookmark text-info"></i> ${aItem.msg}</div>
                                    <div class="text-secondary" style="width:150px;"><small><i class="bi bi-clock text-dark"></i> ${timeAgo(aItem.updated_at)}</small></div>
                                </div>
                            </li>
                            `;
                        })}
                        ${activity.length === 0 ? html`<li class="list-group-item">
                            <div class="text-dark text-center">No activity yet.</div>
                        </li>` : null}
                    </ul>                    
                </div>
            </div>
            <div class="collapse" id="collapse-log-item-messages-${_id}">            
                <div class="card">
                    <ul class="list-group list-group-flush">
                        ${messages.map(message => {
                            return html`
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="text-dark"><i class="bi bi-chat-right-dots text-info"></i> ${message.msg}</div>
                                    <div class="text-secondary" style="width:150px;"><small><i class="bi bi-clock text-dark"></i> ${timeAgo(message.updated_at)}</small></div>
                                </div>
                            </li>
                            `;
                        })}
                        ${messages.length === 0 ? html`<li class="list-group-item">
                            <div class="text-dark text-center">No messages yet.</div>
                        </li>` :null}
                    </ul>                    
                </div>
            </div>
            <div class="mb-2"/>
            <ul class="log-data-info">
                <li class="text-secondary"><${CodeType} ...${{ source }} /></li>
                <li class="text-secondary divider"></li>
                <li class="text-secondary"><i class="bi bi-clock text-dark"></i> ${timeAgo(updated_at)} - First event: ${timeAgo(created_at)}</li>
                <li class="text-secondary divider"></li>                
                <li class="text-secondary">
                    <i class="bi bi-bar-chart text-dark"></i> Detected <span class="text-dark">${count}</span> times
                </li>
                <li class="text-secondary divider"></li>
                <li class="text-secondary">
                    <a class="text-secondary" data-bs-toggle="collapse" href="#collapse-log-item-${_id}" role="button"
                        aria-expanded="false" aria-controls="collapse-log-item-${_id}" style="text-decoration:none;">
                        <i class="bi bi-code-square text-dark"></i> View JSON
                    </a>
                </li>
                <li class="text-secondary divider"></li>
                <li class="text-secondary">
                    <a class="text-secondary" data-bs-toggle="collapse" href="#collapse-log-item-messages-${_id}" role="button"
                        aria-expanded="false" aria-controls="collapse-log-item-messages-${_id}" style="text-decoration:none;">
                        <i class="bi bi-chat-left-text text-dark"></i> View messages
                    </a>
                </li>
                <li class="text-secondary divider"></li>
                <li class="text-secondary">
                    <a class="text-secondary" data-bs-toggle="collapse" href="#collapse-log-item-activity-${_id}" role="button" aria-expanded="false" aria-controls="collapse-log-item-activity-${_id}" style="text-decoration:none;">
                        <i class="bi bi-list-nested text-dark"></i> View activity
                    </a>
                </li>
            </ul>
        </div>
    `;
}