<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\Transaksi_model; // Assuming you have this model

class Dashboard extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session']; // Ensure the session helper is loaded
    protected Transaksi_model $transaksiModel; // Declare the property for the model

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Initialize the model in the constructor
        $this->transaksiModel = new Transaksi_model();
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // E.g.: $this->session = \Config\Services::session();
    }

    public function index()
    {
        $session = session(); // Get the session service instance

        // Check if the 'status' session variable is set to 'login'
        if ($session->get('status') == 'login') {
            // If logged in, load the dashboard view
            return view('dashboard');
        } else {
            // If not logged in, load the login view
            return view('login');
        }
    }
    /**
     * Mengambil data penjualan bulanan untuk grafik.
     * Akan menerima array hari dari frontend dan mengembalikan total penjualan untuk setiap hari.
     *
     * @return ResponseInterface
     */
    public function penjualan_bulan(): ResponseInterface
    {
        $days = $this->request->getPost('day');

        // Ensure $days is an array, handling potential comma-separated string from AJAX
        if (!is_array($days)) {
            $days = explode(',', (string) $days); // Cast to string to avoid error if $days is null
            $days = array_filter($days, 'is_numeric'); // Filter out non-numeric values
        }

        $monthlySalesData = [];
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Iterate through each day received from the frontend (which effectively represents 1 to max_day_in_month)
        // Note: Your getDays() in JS returns 0,1,2...max_day. We'll use this to iterate.
        foreach ($days as $day) {
            if ($day == 0) { // Day 0 is usually an anomaly or placeholder for chart, skip it.
                $monthlySalesData[] = 0; // Add a zero for the 0th index if the chart expects it.
                continue;
            }

            // Construct the date string in 'DD MM YYYY' format as expected by your model's penjualanBulan method
            $dateFormatted = sprintf('%02d %02d %04d', $day, $currentMonth, $currentYear);

            // Call the model method for each specific day
            $dailyTotal = $this->transaksiModel->penjualanBulan($dateFormatted);

            // The model returns an array of sums for each row's 'qty' string.
            // We need the *sum of these sums* to get the total for the day.
            $monthlySalesData[] = array_sum($dailyTotal);
        }

        // If your getDays() in JS includes '0', this array will also have a value for index 0.
        // Charts usually start from index 1 for Day 1. Ensure your chart can handle this or adjust `getDays()`.
        return $this->response->setJSON($monthlySalesData);
    }
}
