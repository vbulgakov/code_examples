<div class="container" :key="`dates`">
    <div class="panel panel-default checked">
        <div class="panel-heading">Booking Dates</div>
        <div class="panel-body">
            <div class="form-group row">
                @foreach($status_fields as $field)
                    <div class="col-md-4 booking-date" v-if="!booking.external || '{{$field}}' !== 'earliest_return_date'">
                        <label for="{{$field}}">@lang('booking.labels.'.$field)</label>
                        <info-popup
                                :icon="'fa-question-circle-o'">@lang('booking.infos.'.$field)</info-popup>
                        <div class="input-group">
                            <date-picker data-removable
                                         v-model="date['{{$field}}']"
                                         :config="pickerConfig"
                                         autocomplete='off'
                                         id="{{$field}}">
                            </date-picker>
                            <span class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>