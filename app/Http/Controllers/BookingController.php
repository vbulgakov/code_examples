<?php

namespace TradeTree\Http\Controllers\Bookings;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TradeTree\Http\Controllers\Controller;
use TradeTree\Http\Requests\Bookings\BookingSearchRequest;
use TradeTree\Http\Requests\Bookings\SaveBookingRateRequest;
use TradeTree\Http\Requests\Bookings\SaveBookingRequest;
use TradeTree\Models\Bookings\Booking;
use TradeTree\Models\Bookings\BookingRate;
use TradeTree\Models\Currency;
use TradeTree\Models\Trades\AddressBook\Location;
use TradeTree\Models\Trades\Uoms\Uom;
use TradeTree\Services\Bookings\BookingService;

class BookingController extends Controller
{
    protected $user;

    protected $company;

    protected $service;

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->company = $this->user->company;
            $this->service = new BookingService($this->user, $this->company);

            return $next($request);
        });
    }

    public function index()
    {
        $statuses = Booking::getStatuses();
        return view('bookings.index', compact('statuses'));
    }

    public function getBookings(BookingSearchRequest $request, $status = null)
    {
        return $this->service->getBookings($request->external, $request->search, $status)->paginate(15);
    }

    public function getRate(Request $request)
    {
        return $this->service->getRate($request->all());
    }

    public function setup(Booking $booking = null)
    {
        $booking = $this->service->getSetup($booking);
        $date_fields = collect(Booking::dateFields());
        $status_fields = collect(Booking::statusFields());
        $locations = Location::with('types')->byCompany($this->company)->get();
        $locations->setAppends(['type_ids']);
        return view('bookings.setup', compact('booking', 'date_fields', 'status_fields', 'locations'));
    }

    public function saveSetup(SaveBookingRequest $request, Booking $booking = null)
    {
        $booking = $this->service->saveSetup($booking, $request->all());
        return compact('booking');
    }

    public function destroy(Booking $booking)
    {
        if (!$booking->shipments()->count()) {
            $booking->delete();
            return ['success' => true];
        }
        $errors = ['errors' => ["This booking is already attached to shipments and can not be deleted"]];
        throw new HttpResponseException(response()->json($errors, 422));
    }

    public function details(Booking $booking)
    {
        $booking->load(['carrier', 'shipping', 'destination']);
        return compact('booking');
    }

    public function bookingRates()
    {
        $company = $this->company;
        return view('bookings.rates', compact('company'));
    }

    public function getRates()
    {
        return BookingRate::byCompany($this->company)->orderBy('month', 'desc')->paginate(15);
    }

    public function viewRate(Request $request, BookingRate $rate = null)
    {
        $company = $this->company;
        if (!$rate->id) {
            $rate->preFill();
            $usd = Currency::getBase();
            $rate->currency_id = $usd->bmc_id;
            $rate->currency_rate = 1;
            $container = Uom::getContainer();
            $rate->uom_id = $container->id;
        }
        return view('bookings.rate', compact('rate', 'company'));
    }

    public function storeRate(SaveBookingRateRequest $request, BookingRate $rate = null)
    {
        $rate = BookingRate::updateOrCreate([
            'id' => $request->rate['id'] ?? null
        ], array_merge($request->rate, [
            'company_id' => $this->company->id,
        ]));
        return compact('rate');
    }

    public function uploadRates(Request $request){
        return $this->service->uploadRates($request->file);
    }

    public function deleteRate(Request $request, BookingRate $rate)
    {
        $rate->delete();
        return ['success' => true];
    }
}
