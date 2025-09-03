<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessKRAItemCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kra:process-item-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process KRA items Code and update the Charge code in SCM';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

 public function handle()
{
    // Get the API URL, username, and password from the config file
    $baseUrl = config('kra.api_url');
    $username = config('kra.api_username');
    $password = config('kra.api_password');

    // Validate that all required configuration values are set
    if (empty($baseUrl) || empty($username) || empty($password)) {
        $this->error('API URL, username, or password is not set in the configuration.');
        return 1; // Exit with an error code
    }

    $apiUrl = rtrim($baseUrl, '/') . '/items';
    $attempt = 1;

    while (true) {
        // Execute the first stored procedure
        $items = DB::select('SET NOCOUNT ON; EXEC dbo.KRA_ItemCodeGeneration');

        Log::info("KRA Command Attempt #{$attempt}: Found " . count($items) . " items");

        if (empty($items)) {
            Log::info("KRA Command: No items found, retrying in 10 minutes...");
            $this->info("No items found, retrying in 10 minutes...");
            sleep(600); // Wait 10 minutes
            $attempt++;
            continue; // Retry loop
        }

        foreach ($items as $item) {
            $itemArray = (array) $item;
            $concatenatedJson = $itemArray[''];
            $jsonArray = '[' . $concatenatedJson . ']';
            $decodedItems = json_decode($jsonArray, true);

            if (is_array($decodedItems)) {
                foreach ($decodedItems as $decodedItem) {
                    Log::info('Processing item:', $decodedItem);

                    try {
                        $response = Http::withBasicAuth($username, $password)
                            ->post($apiUrl, $decodedItem);

                        if ($response->successful()) {
                            $apiResponse = $response->json();
                            Log::info('API Response:', $apiResponse);

                            if (isset($apiResponse['data']['itemCode'])) {
                                $itemCode = $apiResponse['data']['itemCode'];

                                $getResponse = Http::withBasicAuth($username, $password)
                                    ->get($baseUrl . '/items/' . $itemCode);

                                if ($getResponse->successful()) {
                                    $itemDetails = $getResponse->json();
                                    Log::info('Item Details:', $itemDetails);

                                    if (isset($itemDetails['name']) && isset($itemDetails['itemCode'])) {
                                        $name = $itemDetails['name'];
                                        $itemCode = $itemDetails['itemCode'];

                                        $status = '';
                                        $remark = '';

                                        try {
                                            $success = DB::statement('EXEC dbo.KRA_ItemCodeUpdate ?, ?, ?, ?', [
                                                $name,
                                                $itemCode,
                                                &$status,
                                                &$remark
                                            ]);

                                            if ($success && $status === 'Success') {
                                                $this->info("Stored procedure executed successfully for Name: $name, ItemCode: $itemCode. Remark: $remark");
                                                Log::info("Stored procedure executed successfully for Name: $name, ItemCode: $itemCode. Remark: $remark");
                                            } else {
                                                $this->error("Stored procedure failed for Name: $name, ItemCode: $itemCode. Remark: $remark");
                                                Log::error("Stored procedure failed for Name: $name, ItemCode: $itemCode. Remark: $remark");
                                            }
                                        } catch (\Exception $e) {
                                            $this->error("Stored procedure failed for Name: $name, ItemCode: $itemCode. Error: " . $e->getMessage());
                                            Log::error("Stored procedure failed for Name: $name, ItemCode: $itemCode. Error: " . $e->getMessage());
                                        }
                                    } else {
                                        Log::error('Required keys (name or itemCode) missing in GET response:', $itemDetails);
                                        $this->error("Required keys (name or itemCode) missing in GET response: " . json_encode($itemDetails));
                                    }
                                } else {
                                    $errorMessage = $getResponse->body();
                                    Log::error("GET request failed for itemCode: $itemCode. Error: $errorMessage");
                                    $this->error("GET request failed for itemCode: $itemCode");
                                }
                            } else {
                                Log::error('itemCode missing in API response:', $apiResponse);
                                $this->error("itemCode missing in API response: " . json_encode($apiResponse));
                            }
                        } else {
                            $errorMessage = $response->body();
                            Log::error("API request failed for item: " . json_encode($decodedItem) . ". Error: $errorMessage");
                            $this->error("API request failed for item: " . json_encode($decodedItem));
                        }
                    } catch (\Exception $e) {
                        Log::error("Exception during API request for item: " . json_encode($decodedItem) . ". Error: " . $e->getMessage());
                        $this->error("Exception during API request for item: " . json_encode($decodedItem));
                    }
                }
            } else {
                Log::error('Invalid JSON response from stored procedure:', $itemArray);
                $this->error('Invalid JSON response from stored procedure.');
            }
        }

        break; // Exit loop after processing items
    }

    return 0;
}

    // public function handle()
    // {
    //     // Get the API URL, username, and password from the config file
    //     $baseUrl = config('kra.api_url');
    //     $username = config('kra.api_username');
    //     $password = config('kra.api_password');

    //     // Validate that all required configuration values are set
    //     if (empty($baseUrl) || empty($username) || empty($password)) {
    //         $this->error('API URL, username, or password is not set in the configuration.');
    //         return 1; // Exit with an error code
    //     }

    //     // Construct the full API URL by appending the endpoint
    //     $apiUrl = rtrim($baseUrl, '/') . '/items';

    //     // Execute the first stored procedure
    //     $items = DB::select('SET NOCOUNT ON; EXEC dbo.KRA_ItemCodeGeneration');

    //     // Log the raw response for debugging
    //     Log::info('Raw response from stored procedure:', $items);

    //     foreach ($items as $item) {
    //         // Convert the item object to an array
    //         $itemArray = (array) $item;

    //         // Extract the concatenated JSON string from the response
    //         $concatenatedJson = $itemArray[''];

    //         // Wrap the concatenated JSON string in square brackets to make it a valid JSON array
    //         $jsonArray = '[' . $concatenatedJson . ']';

    //         // Decode the JSON array
    //         $decodedItems = json_decode($jsonArray, true);

    //         // Check if the decoded items are valid and is an array
    //         if (is_array($decodedItems)) {
    //             foreach ($decodedItems as $decodedItem) {
    //                 // Log the individual item for debugging
    //                 Log::info('Processing item:', $decodedItem);

    //                 // Make API request with Basic Authentication
    //                 $response = Http::withBasicAuth($username, $password)
    //                     ->post($apiUrl, $decodedItem);

    //                 if ($response->successful()) {
    //                     // API request was successful
    //                     $apiResponse = $response->json();

    //                     // Log the API response for debugging
    //                     Log::info('API Response:', $apiResponse);

    //                     // Check if the API response contains the itemCode in the data object
    //                     if (isset($apiResponse['data']['itemCode'])) {
    //                         $itemCode = $apiResponse['data']['itemCode'];

    //                         // Make a GET request to retrieve item details
    //                         $getResponse = Http::withBasicAuth($username, $password)
    //                             ->get($baseUrl . '/items/' . $itemCode);

    //                         if ($getResponse->successful()) {
    //                             // GET request was successful
    //                             $itemDetails = $getResponse->json();

    //                             // Log the item details for debugging
    //                             Log::info('Item Details:', $itemDetails);

    //                             // Extract the name and itemCode from the GET request response
    //                             if (isset($itemDetails['name']) && isset($itemDetails['itemCode'])) {
    //                                 $name = $itemDetails['name'];
    //                                 $itemCode = $itemDetails['itemCode'];

    //                                 // Execute the second stored procedure
    //                                 $status = '';
    //                                 $remark = '';

    //                                 DB::statement('EXEC dbo.KRA_ItemCodeUpdate ?, ?, ?, ?', [
    //                                     $name,
    //                                     $itemCode,
    //                                     $status,
    //                                     $remark
    //                                 ]);

    //                                 // Output the status and remark
    //                                 $this->info("Name: $name, ItemCode: $itemCode, Status: $status, Remark: $remark");
    //                             } else {
    //                                 // Handle missing keys in the GET request response
    //                                 Log::error('Required keys (name or itemCode) are missing in the GET request response for item:', $itemDetails);
    //                                 $this->error("Required keys (name or itemCode) are missing in the GET request response for item: " . json_encode($itemDetails));
    //                             }
    //                         } else {
    //                             // Handle GET request failure
    //                             $errorMessage = $getResponse->body();
    //                             Log::error("GET request failed for itemCode: $itemCode. Error: $errorMessage");
    //                             $this->error("GET request failed for itemCode: $itemCode");
    //                         }
    //                     } else {
    //                         // Handle missing itemCode in the API response
    //                         Log::error('itemCode is missing in the API response for item:', $apiResponse);
    //                         $this->error("itemCode is missing in the API response for item: " . json_encode($apiResponse));
    //                     }
    //                 } else {
    //                     // Handle API request failure
    //                     $errorMessage = $response->body();
    //                     Log::error("API request failed for item: " . json_encode($decodedItem) . ". Error: $errorMessage");
    //                     $this->error("API request failed for item: " . json_encode($decodedItem));
    //                 }
    //             }
    //         } else {
    //             // Handle invalid JSON response
    //             Log::error('Invalid JSON response from stored procedure:', $itemArray);
    //             $this->error('Invalid JSON response from stored procedure.');
    //         }
    //     }

    //     return 0;
    // }

}
