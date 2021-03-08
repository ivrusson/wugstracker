

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
                    <label class="form-check-label" for="wugstracker_JS_active">Activate javascript debugger.</label>
                </div>
                <small>You need to activate this option to start tracking Javascript errors.</small>
                <hr class="divider" />
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wugstracker_JS_test_active" checked=${options.wugstracker_JS_test_active} onchange=${()=> updateOption('wugstracker_JS_test_active')} />
                    <label class="form-check-label" for="wugstracker_JS_test_active">Activate javascript test script.</label>
                </div>
                <small>This option render the following code that allow you to tracks the first error <span class="badge bg-secondary">thisIsNotAFunction()</span>.</small>
                <pre class="code">
                ${`<script type="text/javascript">thisIsNotAFunction();</script>`}
                </pre>
                <hr class="divider" />
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wugstracker_WP_debug" checked=${options.wugstracker_WP_debug} onchange=${() => updateOption('wugstracker_WP_debug')} />
                    <label class="form-check-label" for="wugstracker_WP_debug">Activate Wordpress debug.</label>
                </div>
                <small>This option activates Wordpress WP_DEBUG configuration.</small>
                <hr class="divider" />
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wugstracker_WP_log" checked=${options.wugstracker_WP_log} onchange=${() => updateOption('wugstracker_WP_log')} />
                    <label class="form-check-label" for="wugstracker_WP_log">Activate Wordpress debug log.</label>
                </div>
                <small>This option activates Wordpress WP_DEBUG_LOG configuration.</small>
                <hr class="divider" />
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wugstracker_WP_display" checked=${options.wugstracker_WP_display} onchange=${() => updateOption('wugstracker_WP_display')} />
                    <label class="form-check-label" for="wugstracker_WP_display">Activate Wordpress debug diplay.</label>
                </div>
                <small>This option activates Wordpress WP_DEBUG_DISPLAY configuration.</small>
            </div>
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">PHP configuration</h5>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wugstracker_PHP_active" checked=${options.wugstracker_PHP_active} onchange=${() => updateOption('wugstracker_PHP_active')} />
                    <label class="form-check-label" for="wugstracker_PHP_active">Activate PHP debugger.</label>
                </div>
                <small>You need to activate this option to start tracking the PHP from "set_error_handler() function".</small>
            </div>
            <${Messages} messages=${messages} />
        </div>
    </div>`;
};