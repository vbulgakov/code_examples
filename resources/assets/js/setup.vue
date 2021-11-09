<script>
    import infiniteScroll from 'vue-infinite-scroll';
    import Switches from "vue-switches";
    import DatePicker from 'vue-bootstrap-datetimepicker';
    import AutoComplete from "./../general/autocomplete";
    import {datePickerMixin} from "../mixins/datePickerMixin";
    import {errorsMixin} from "../auctions/mixins/common/errorsMixin";
    import {dateStatusMixin} from "../mixins/dateStatusMixin";
    import {addressBookMixin} from "../trades/mixins/addressBookMixin";
    import {bookingConversionMixin} from "./mixins/bookingConversionMixin";

    export default {
        name: "setup",
        directives: {infiniteScroll},
        props: ['model'],
        mixins: [datePickerMixin, errorsMixin, dateStatusMixin, addressBookMixin, bookingConversionMixin],
        components: {
            DatePicker, AutoComplete, Switches,
        },
        data() {
            return {
                booking: this.model,
                changed: false,
                allow_past: true,
                tab: 'setup',
            };
        },
        methods: {
            save() {
                if (this.changed) {
                    spinner();
                    let url = '/bookings/setup' + (this.booking.id ? `/${this.booking.id}` : '');
                    return this.$http.post(url, {book: this.booking}).then((response) => {
                        this.booking = this.date = response.body.booking;
                        this.setUrl("Booking saved");
                        swal({
                            type: 'success',
                            title: 'Booking saved successfully!',
                        });
                    }, (error) => {
                        this.errors = error.body;
                        this.popUpErrors(error.body, 'Error(s)!');
                    }).finally(function () {
                        spinner(false);
                    });
                }
                this.emptyPopup();
            },
            activate(tab) {
                this.tab = tab;
            },
            isActive(tab) {
                return this.tab === tab;
            },
            loadRate() {
                spinner();
                this.$http.get('/bookings/get-rate', this.rate_query).then((response) => {
                    let rate = response.data.rate;
                    if (!rate) {
                        return swal({
                            title: 'Sorry!',
                            text: "No rate found for this booking settings",
                            type: 'info'
                        });
                    }
                    this.booking.freight_rate = rate.freight_rate;
                    this.booking.freight_rate_original = rate.freight_rate_original;
                    this.booking.uom_id = rate.uom_id;
                    this.booking.currency_id = rate.currency_id;
                    this.booking.currency_rate = rate.currency_rate;
                }, (error) => {
                    this.popUpErrors(error.body, 'Error(s)!');
                }).finally(() => spinner(false));
            },
            exclude(field) {
                return _.includes(['booking_request', 'booking_received', 'booking_sent',], field);
            },
        },
        computed: {
            load_available() {
                let shipping = this.booking.shipping.name && this.booking.shipping.name.length,
                    destination = this.booking.destination.name && this.booking.destination.name.length,
                    carrier = this.booking.carrier.name && this.booking.carrier.name.length;

                return shipping && destination && carrier;
            },
            locationUrl() {
                return 'v1/location-suggestions?user_company=' + this.booking.company_id + '&location=';
            },
            carrierUrl() {
                return 'v1/carrier-suggestions?carrier=';
            },
            vesselUrl() {
                return 'v1/vessel-suggestions?vessel=';
            },
            setupUrl() {
                return `/bookings/setup/${this.booking.id}`;
            },
            rate_query() {
                return {
                    params: {
                        pol: this.booking.shipping.name,
                        pod: this.booking.destination.name,
                        carrier: this.booking.carrier.name
                    }
                };
            },
        },
        watch: {
            booking: {
                deep: true,
                handler() {
                    this.changed = true;
                }
            },
            'booking.freight_rate_original': {
                handler() {
                    this.setFreightRate();
                }
            },
        }
    }
</script>