(() => {
    const dataNode = document.getElementById('dashboard-data');
    if (!dataNode) {
        return;
    }

    const dashboard = JSON.parse(dataNode.textContent || '{}');
    const vendors = dashboard.vendors || [];
    const distribution = dashboard.distribution || {};

    const buildRow = ({ label, value, percent, suffix = '' }) => {
        const row = document.createElement('div');
        row.className = 'chart-row';
        row.innerHTML = `
            <span class="chart-label"></span>
            <span class="chart-track"><span class="chart-fill"></span></span>
            <span class="chart-value"></span>
        `;

        row.querySelector('.chart-label').textContent = label;
        row.querySelector('.chart-value').textContent = `${value}${suffix}`;
        requestAnimationFrame(() => {
            row.querySelector('.chart-fill').style.width = `${Math.max(0, Math.min(100, percent))}%`;
        });

        return row;
    };

    const averageChart = document.getElementById('averageChart');
    if (averageChart) {
        vendors.forEach((vendor) => {
            averageChart.appendChild(buildRow({
                label: vendor.name,
                value: Number(vendor.average).toFixed(1),
                percent: (Number(vendor.average) / 5) * 100,
                suffix: ' ★'
            }));
        });
    }

    const reviewChart = document.getElementById('reviewChart');
    if (reviewChart) {
        const maxReviews = Math.max(1, ...vendors.map((vendor) => Number(vendor.reviews)));
        vendors.forEach((vendor) => {
            reviewChart.appendChild(buildRow({
                label: vendor.name,
                value: Number(vendor.reviews),
                percent: (Number(vendor.reviews) / maxReviews) * 100
            }));
        });
    }

    const distributionChart = document.getElementById('ratingDistribution');
    if (distributionChart) {
        const values = [1, 2, 3, 4, 5].map((rating) => Number(distribution[rating] || 0));
        const max = Math.max(1, ...values);

        [1, 2, 3, 4, 5].forEach((rating) => {
            const total = Number(distribution[rating] || 0);
            const bar = document.createElement('div');
            bar.className = 'distribution-bar';
            bar.innerHTML = '<span></span><span></span>';
            bar.querySelector('span:first-child').textContent = total;
            bar.querySelector('span:last-child').textContent = `${rating} ★`;
            requestAnimationFrame(() => {
                bar.querySelector('span:first-child').style.height = `${Math.max(8, (total / max) * 100)}%`;
            });
            distributionChart.appendChild(bar);
        });
    }
})();

