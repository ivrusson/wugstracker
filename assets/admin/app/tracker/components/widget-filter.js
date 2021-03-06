/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

export const WidgetFilter = ({ state, dispatch }) => {
    const { loading } = state;

    return html`
        <div class="card">
        </div>
    `;
};