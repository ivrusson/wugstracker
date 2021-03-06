

/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);
const React = preactCompat;

const Messages = ({messages = {}}) => {
    if(messages.length > 0) {
        return html`
            <div class="$1">
                ${messages.map(item => {
                    return html`<div class="alert alert-${item.type === 'success' ? 'success' : 'danger'}" role="alert" style="margin-top:25px;">${item.message}</div>`;
                })}
            </div>
        `;
    }
    return;
}

export const Options = ({ state, dispatch }) => {
    const { options, messages } = state;

    console.log('Options', state);

    

    const updateOption = async (name) => {
        dispatch({ type: 'loading', payload: true });

        let result = await WugsApi.fetch({ method: 'PUT', path: 'options', data: { options: { [name]: !options[name] } } });

        if (result) {
            if (result.hasOwnProperty('options')) {
                dispatch({ type: 'set_options', payload: result.options });
            }
            if (result.hasOwnProperty('messages')) {
                dispatch({ type: 'set_messages', payload: result.messages });
            }
        }

        setTimeout(() => {
            dispatch({ type: 'remove_messages' });
        }, 5000);
    }

    return html`<div>
        <div class="list-group">
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Javascript configuration</h5>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wugstracker_JS_active" checked=${options.wugstracker_JS_active} onchange=${() => updateOption('wugstracker_JS_active')} />
                    <label class="form-check-label" for="wugstracker_JS_active">Activate javascript debugger. ${options.wugstracker_JS_active === true ? html`<b style="color:green">(Option active)</b>` : ''}</label>
                </div>
                <small>You need to activate this option to start tracking Javascript errors.</small>
            </div>
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">PHP configuration</h5>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wugstracker_PHP_active" checked=${options.wugstracker_PHP_active} onchange=${() => updateOption('wugstracker_PHP_active')} />
                    <label class="form-check-label" for="wugstracker_PHP_active">Activate PHP debugger. ${options.wugstracker_PHP_active === true ? html`<b style="color:green">(Option active)</b>` : ''}</label>
                </div>
                <small>You need to activate this option to start tracking the PHP from "set_error_handler() function".</small>
            </div>
            <${Messages} messages=${messages} />
        </div>
    </div>`;
};