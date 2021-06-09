<!-- bootstrap datepicker input -->

<?php
    // if the column has been cast to Carbon or Date (using attribute casting)
    // get the value as a date string
    if (isset($field['value']) && ($field['value'] instanceof \Carbon\CarbonInterface)) {
        $field['value'] = $field['value']->format('Y-m-d');
    }

    $field['value'] = oldValueDefaultOrFallback($field, '');
    $field['attributes']['style'] = $field['attributes']['style'] ?? 'background-color: white!important;';
    $field['attributes']['readonly'] = $field['attributes']['readonly'] ?? 'readonly';

    $field_language = isset($field['date_picker_options']['language']) ? $field['date_picker_options']['language'] : \App::getLocale();
?>

@include('crud::fields.inc.wrapper_start')
    <input type="hidden" class="form-control" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <div class="input-group date">
        <input
            data-bs-datepicker="{{ isset($field['date_picker_options']) ? json_encode($field['date_picker_options']) : '{}'}}"
            data-init-function="bpFieldInitDatePickerElement"
            type="text"
            @include('crud::fields.inc.attributes')
            >
        <div class="input-group-append">
            <span class="input-group-text">
                <span class="la la-calendar"></span>
            </span>
        </div>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <link rel="stylesheet" href="{{ asset('packages/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}">
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script src="{{ asset('packages/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    @if ($field_language !== 'en')
        <script charset="UTF-8" src="{{ asset('packages/bootstrap-datepicker/dist/locales/bootstrap-datepicker.'.$field_language.'.min.js') }}"></script>
    @endif
    <script>
        if (jQuery.ui) {
            var datepicker = $.fn.datepicker.noConflict();
            $.fn.bootstrapDP = datepicker;
        } else {
            $.fn.bootstrapDP = $.fn.datepicker;
        }

        var dateFormat=function(){var a=/d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,b=/\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,c=/[^-+\dA-Z]/g,d=function(a,b){for(a=String(a),b=b||2;a.length<b;)a="0"+a;return a};return function(e,f,g){var h=dateFormat;if(1!=arguments.length||"[object String]"!=Object.prototype.toString.call(e)||/\d/.test(e)||(f=e,e=void 0),e=e?new Date(e):new Date,isNaN(e))throw SyntaxError("invalid date");f=String(h.masks[f]||f||h.masks.default),"UTC:"==f.slice(0,4)&&(f=f.slice(4),g=!0);var i=g?"getUTC":"get",j=e[i+"Date"](),k=e[i+"Day"](),l=e[i+"Month"](),m=e[i+"FullYear"](),n=e[i+"Hours"](),o=e[i+"Minutes"](),p=e[i+"Seconds"](),q=e[i+"Milliseconds"](),r=g?0:e.getTimezoneOffset(),s={d:j,dd:d(j),ddd:h.i18n.dayNames[k],dddd:h.i18n.dayNames[k+7],m:l+1,mm:d(l+1),mmm:h.i18n.monthNames[l],mmmm:h.i18n.monthNames[l+12],yy:String(m).slice(2),yyyy:m,h:n%12||12,hh:d(n%12||12),H:n,HH:d(n),M:o,MM:d(o),s:p,ss:d(p),l:d(q,3),L:d(q>99?Math.round(q/10):q),t:n<12?"a":"p",tt:n<12?"am":"pm",T:n<12?"A":"P",TT:n<12?"AM":"PM",Z:g?"UTC":(String(e).match(b)||[""]).pop().replace(c,""),o:(r>0?"-":"+")+d(100*Math.floor(Math.abs(r)/60)+Math.abs(r)%60,4),S:["th","st","nd","rd"][j%10>3?0:(j%100-j%10!=10)*j%10]};return f.replace(a,function(a){return a in s?s[a]:a.slice(1,a.length-1)})}}();dateFormat.masks={default:"ddd mmm dd yyyy HH:MM:ss",shortDate:"m/d/yy",mediumDate:"mmm d, yyyy",longDate:"mmmm d, yyyy",fullDate:"dddd, mmmm d, yyyy",shortTime:"h:MM TT",mediumTime:"h:MM:ss TT",longTime:"h:MM:ss TT Z",isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"},dateFormat.i18n={dayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"]},Date.prototype.format=function(a,b){return dateFormat(this,a,b)};

        function bpFieldInitDatePickerElement(element) {
            var $fake = element,
                $field = $fake.closest('.input-group').parent().find('input[type="hidden"]'),
            $customConfig = $.extend({
                format: 'dd/mm/yyyy'
            }, $fake.data('bs-datepicker'));
            $picker = $fake.bootstrapDP($customConfig);

            var $existingVal = $field.val();

                if( $existingVal && $existingVal.length ){
                    // Passing an ISO-8601 date string (YYYY-MM-DD) to the Date constructor results in
                    // varying behavior across browsers. Splitting and passing in parts of the date
                    // manually gives us more defined behavior.
                    // See https://stackoverflow.com/questions/2587345/why-does-date-parse-give-incorrect-results
                    var parts = $existingVal.split('-');
                    var year = parts[0];
                    var month = parts[1] - 1; // Date constructor expects a zero-indexed month
                    var day = parts[2];
                    preparedDate = new Date(year, month, day).format($customConfig.format);
                    $fake.val(preparedDate);
                    $picker.bootstrapDP('update', preparedDate);
                }

                // prevent users from typing their own date
                // since the js plugin does not support it
                // $fake.on('keydown', function(e){
                //     e.preventDefault();
                //     return false;
                // });

            $picker.on('show hide change', function(e){
                if( e.date ){
                    var sqlDate = e.format('yyyy-mm-dd');
                } else {
                    try {
                        var sqlDate = $fake.val();

                        if( $customConfig.format === 'dd/mm/yyyy' ){
                            sqlDate = new Date(sqlDate.split('/')[2], sqlDate.split('/')[1] - 1, sqlDate.split('/')[0]).format('yyyy-mm-dd');
                        }
                    } catch(e){
                        if( $fake.val() ){
                                new Noty({
                                    type: "error",
                                    text: "<strong>Whoops!</strong><br>Sorry we did not recognise that date format, please make sure it uses a yyyy mm dd combination"
                                  }).show();
                            }
                        }
                    }

                    $field.val(sqlDate);
                });
            }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
