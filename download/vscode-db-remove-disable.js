(function() {
    function getActiveTabInfo() {
        const activeTab = document.querySelector('.tabs-container .tab.active');
        if (!activeTab) return null;

        const label = activeTab.querySelector('.tab-label');
        const fileName = label ? label.textContent.trim() : '';
        const title = activeTab.getAttribute('title') || '';
        return { fileName, title };
    }

    function logCurrentTab() {
        const info = getActiveTabInfo();
        if (info) {
            console.log(`当前标签：${info.fileName} | 完整信息：${info.title}`);
        } else {
            console.log('未找到活动标签');
        }
    }
    console.log('test');
    // // 初始输出
    // setTimeout(logCurrentTab, 1000);

    // // 监听切换
    // const container = document.querySelector('.tabs-container');
    // if (container) {
    //     const observer = new MutationObserver(() => logCurrentTab());
    //     observer.observe(container, { attributes: true, subtree: true, attributeFilter: ['class'] });
    // }
})();