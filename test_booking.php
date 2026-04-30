<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get available unit
$unit = App\Models\Unit::where('status', 'tersedia')->first();
if (!$unit) {
    echo "No available unit.\n";
    exit;
}

$request = Illuminate\Http\Request::create('/bookings', 'POST', [
    'unit_id' => $unit->id,
    'tanggal_mulai' => date('Y-m-d'),
    'tanggal_selesai_rencana' => date('Y-m-d', strtotime('+3 days'))
]);
$request->setLaravelSession(app('session.store'));

$user = App\Models\User::first();
auth()->login($user);

try {
    $response = app(App\Http\Controllers\BookingController::class)->store($request);
    dump($response->getSession()->get('errors'));
    echo "Status code: " . $response->getStatusCode() . "\n";
    echo "Target URL: " . $response->getTargetUrl() . "\n";
    echo "Success Message: " . $response->getSession()->get('success') . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
