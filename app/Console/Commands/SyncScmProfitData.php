<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SyncBookingData extends Command
{
    protected $signature = 'sync:bookingdata';
    protected $description = 'Push booking XML to SCM Booking API and update CRM_SCMprofit_integration';

    public function handle()
    {
        Log::channel('booking')->info('Starting booking sync at ' . Carbon::now());

        try {
            // Fetch unprocessed quote requests
            $bookings = DB::connection('sqlsrv')
                ->table('CRM_SCMprofit_integration')
                ->select('clientid', 'bookingno', 'SCMBookingXML')
                ->whereNotNull('SCMBookingXML')
                ->whereNull('SCMBookingID')
                ->whereNull('SCMBookingNo')
                ->get();

            if ($bookings->isEmpty()) {
                Log::channel('booking')->info('No unprocessed quote requests found.');
                $this->info('No unprocessed quote requests found.');
                return;
            }

            foreach ($bookings as $booking) {
                $this->processBooking($booking);
            }

            Log::channel('booking')->info('✅ Booking sync completed successfully!');
            $this->info('✅ Booking sync completed successfully!');
        } catch (\Exception $e) {
            Log::channel('booking')->error('Booking sync failed: ' . $e->getMessage());
            $this->error('Booking sync failed: ' . $e->getMessage());
        }
    }

    protected function processBooking($booking)
    {
        try {
            // Validate XML
            if (empty($booking->SCMBookingXML) || !simplexml_load_string($booking->SCMBookingXML)) {
                throw new \Exception('Invalid XML for client ID: ' . $booking->clientid);
            }

            // SCM Booking API details
            $apiUrl = 'https://scm.fitexpress.com/api/booking'; // Replace with actual endpoint
            $username = 'your-username'; // Replace with actual username
            $password = 'your-password'; // Replace with actual password

            // Send XML to SCM Booking API with Basic Auth
            $response = Http::withBasicAuth($username, $password)
                ->withHeaders([
                    'Content-Type' => 'application/xml',
                ])
                ->withBody($booking->SCMBookingXML, 'application/xml')
                ->timeout(30)
                ->post($apiUrl);

            if ($response->successful()) {
                // Handle success response
                $responseData = $response->json(); // Adjust for XML if needed
                $scmBookingId = $responseData['id'] ?? null; // Adjust field name
                $scmBookingNo = $responseData['no'] ?? $booking->bookingno; // Fallback to bookingno

                if (!$scmBookingId) {
                    throw new \Exception('Missing SCMBookingID in response');
                }

                // Update CRM_SCMprofit_integration (success)
                DB::connection('sqlsrv')
                    ->table('CRM_SCMprofit_integration')
                    ->where('clientid', $booking->clientid)
                    ->where('bookingno', $booking->bookingno)
                    ->update([
                        'SCMBookingID' => $scmBookingId,
                        'SCMBookingNo' => $scmBookingNo,
                        'SCMINTStatus' => 'Success',
                        'updatedon' => Carbon::now(),
                    ]);

                Log::channel('booking')->info("Successfully processed booking for client ID: {$booking->clientid}, BookingNo: {$booking->bookingno}, SCMBookingID: {$scmBookingId}");
            } else {
                // Handle failure response
                $errorMessage = $response->json()['message'] ?? 'Unknown API error: ' . $response->body();
                $errorMessage = substr($errorMessage, 0, 255); // Truncate to fit SCMINTStatus

                // Update CRM_SCMprofit_integration (failure)
                DB::connection('sqlsrv')
                    ->table('CRM_SCMprofit_integration')
                    ->where('clientid', $booking->clientid)
                    ->where('bookingno', $booking->bookingno)
                    ->update([
                        'SCMINTStatus' => $errorMessage,
                        'updatedon' => Carbon::now(),
                    ]);

                Log::channel('booking')->error("API failed for client ID: {$booking->clientid}, BookingNo: {$booking->bookingno}. Error: {$errorMessage}");
            }
        } catch (\Exception $e) {
            // Handle local errors (e.g., invalid XML, network issues)
            $errorMessage = substr($e->getMessage(), 0, 255); // Truncate
            DB::connection('sqlsrv')
                ->table('CRM_SCMprofit_integration')
                ->where('clientid', $booking->clientid)
                ->where('bookingno', $booking->bookingno)
                ->update([
                    'SCMINTStatus' => $errorMessage,
                    'updatedon' => Carbon::now(),
                    ]);

                Log::channel('booking')->error("Failed to process booking for client ID: {$booking->clientid}, BookingNo: {$booking->bookingno}. Error: {$e->getMessage()}");
            }
        }
    }
