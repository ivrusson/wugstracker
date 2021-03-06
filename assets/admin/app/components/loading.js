

/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

export const Loading = () => {
    return html`<div class="d-flex justify-content-center" style="margin: 150px 0;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden"></span>
        </div>
    </div>`;
}