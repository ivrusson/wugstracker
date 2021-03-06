

/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

import { Menu } from './menu.js';
import { LogList } from './log-list.js';

export const Container = ({ state, dispatch }) => {
    return html`
    <div class="row">
        <div class="col-12">
            <div class="mb-4"/>
            <${Menu} ...${{ state, dispatch }} />
            <div class="mb-4"/>
        </div>
        <div class="col-12">
            <${LogList} ...${{ state, dispatch }}/>
        </div>
    </div>             
    `;
};