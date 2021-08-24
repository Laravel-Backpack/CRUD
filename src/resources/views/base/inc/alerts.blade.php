{{-- Bootstrap Notifications using Prologue Alerts & PNotify JS --}}
<script type="text/javascript">
    // This is intentionaly run after dom loads so this way we can avoid showing duplicate alerts
    // when the user is beeing redirected by persistent table, that happens before this event triggers.
    document.onreadystatechange = function () {
        if (document.readyState == "interactive") {
            Noty.overrideDefaults({
                layout: 'topRight',
                theme: 'backstrap',
                timeout: 2500,
                closeWith: ['click', 'button'],
            });

            // get alerts from the alert bag
            var $alerts_from_php = JSON.parse('@json(\Alert::getMessages())');

            // get the alerts from the localstorage
            var $alerts_from_localstorage = JSON.parse(localStorage.getItem('backpack_alerts'))
                ? JSON.parse(localStorage.getItem('backpack_alerts')) : {};

            // merge both php alerts and localstorage alerts
            Object.entries($alerts_from_php).forEach(function(type) {

                if(typeof $alerts_from_localstorage[type[0]] !== 'undefined') {
                    type[1].map(function(msg) {
                        $alerts_from_localstorage[type[0]].push(msg);
                    });
                } else {
                    $alerts_from_localstorage[type[0]] = type[1];
                }
            });

            for (var type in $alerts_from_localstorage) {
                let messages = new Set($alerts_from_localstorage[type]);

                messages.forEach(function(text) {
                    let alert = {};
                    alert['type'] = type;
                    alert['text'] = text;
                    new Noty(alert).show()
            });
            }

            // in the end, remove backpack alerts from localStorage
            localStorage.removeItem('backpack_alerts');
        }
    };
</script>
