var elements = Array.from(document.getElementsByClassName("delivery-timer"));

elements.forEach(function (el) {
    var wrapper = el.closest('.timer-wrapper');
    var target = wrapper ? wrapper.getAttribute('data-target') : null;
    var deliveryId = wrapper ? wrapper.getAttribute('data-id') : null;
    if (!target || !deliveryId) return;

    (function (el, targetDate, id) {
        function updateCountdown() {
            var now = new Date();
            var end = new Date(targetDate);

            if (end <= now) {
                var elapsed = intervalToDuration({ start: end, end: now });
                var elapsedText = formatDuration(elapsed, {
                    format: ['years', 'months', 'days', 'hours', 'minutes'],
                    zero: true,
                    delimiter: ' ',
                    locale: idLocale
                });

                elapsedText = elapsedText
                    .replace(/(\d+) days?/, '$1 Hari')
                    .replace(/(\d+) hours?/, '$1 Jam')
                    .replace(/(\d+) minutes?/, '$1 Menit');

                el.innerHTML = `<small style="color:#dc3545;">${elapsedText} yang lalu</small>`;
                return;
            }

            var duration = intervalToDuration({ start: now, end: end });
            el.textContent = formatDuration(duration, {
                format: ['years', 'months', 'days', 'hours', 'minutes'],
                zero: true,
                delimiter: ' ',
                locale: idLocale
            });
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    })(el, target, deliveryId);
});