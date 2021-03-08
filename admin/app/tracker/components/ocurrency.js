/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

export const Ocurrency = ({ log }) => {

    const buildBars = () => {
        let today = luxon.DateTime.fromJSDate(new Date());
        let pastTenDays = [];
        for(let i = 9; i > 0; i--) {
            pastTenDays.push(today.minus({ days: i+1 }));
        }
        pastTenDays.push(today);

        let itemsByDay = {};
        pastTenDays.forEach(dateTime => {
            itemsByDay[dateTime.toISODate()] = {
                count: 0,
                lastEvent: dateTime,
            };
        });

        let putInArray = (item) => {
            let dateTime = luxon.DateTime.fromJSDate(new Date(item.created_at.date));
            if (itemsByDay[dateTime.toISODate()]) {
                itemsByDay[dateTime.toISODate()] = {
                    count: itemsByDay[dateTime.toISODate()].count+1,
                    lastEvent: dateTime.toMillis() > itemsByDay[dateTime.toISODate()].lastEvent.toMillis() ? dateTime : itemsByDay[dateTime.toISODate()].lastEvent,
                }
            }
        }

        putInArray(log);
        if (log.childs.length > 0) {
            log.childs.forEach(child => {
                putInArray(child);
            });
        }

        let maxOcurrenciesPerDay = 70;
        let maxCountDetected = 0;
        let ocurrencies = [
            { color: 'default', bounds: [0, 5] },
            { color: 'green', bounds: [6, 10] },
            { color: 'yellow', bounds: [11, 30] },
            { color: 'orange', bounds: [31, 50] },
            { color: 'red', bounds: [51, 70] }
        ];

        Object.keys(itemsByDay).forEach(key => {
            const { count } = itemsByDay[key]
            let color = 'default';
            ocurrencies.forEach(ocu => {
                if (count >= ocu.bounds[0] && count <= ocu.bounds[1]) {
                    color = ocu.color
                }
            });
            if(count >= maxOcurrenciesPerDay) {
                color = 'red';
            }
            maxCountDetected = count > maxCountDetected ? count : maxCountDetected;
            itemsByDay[key].color = color;
        });

        return {
            maxCountDetected,
            maxOcurrenciesPerDay,
            itemsByDay
        }
    }

    const chart = buildBars();

    return html`
        <div class="ocurrency-chart">
            ${Object.keys(chart.itemsByDay).map(key => {
                let bar = chart.itemsByDay[key];
                let height = Math.ceil(bar.count * 100 / chart.maxOcurrenciesPerDay);
                let styles = { style: { height: height +"%" } };
                return html`
                    <div class="bar" data-bs-toggle="tooltip" data-bs-placement="top" title="${bar.lastEvent.toISODate()} | Count: ${bar.count}">
                        <div class="inner-bar ${bar.color}" ...${styles}></div>
                    </div>
                `;
            })}
        </div>
    `;
};