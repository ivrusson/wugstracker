/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

export const Footer = ({ }) => {

    return html`
        <div>
            <p class="text-center" style="margin-top:50px;">
                <small>
                    Plugin created by
                    [<a href="https://github.com/ivrusson" target="_blank" style="text-decoration: unset;">
                        <img src="https://avatars.githubusercontent.com/u/3583352?s=20&v=4" class="rounded-circle" height="20" width="20"/>
                        <em style="color:#b6477a;margin-left:3px;">Ivrusson</em>
                    </a>]
                </small>
            </p>
        </div>
    `;
}