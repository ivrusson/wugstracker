/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

export const Alerts = (messages = []) => {
    if(messages.length > 0) {
        return html`
            <div class="alerts-wrapper">
                ${messages.map(item => {
                    return html`
                        <div class="alert alert-${item.type === 'success' ? 'success' : 'danger'}" role="alert" style="margin-top:25px;">
                            ${item.message}
                            <button type="button" class="btn-close" aria-label="Close"></button>
                        </div>
                    `;
                })}
            </div>
        `;
    }
    return;
}