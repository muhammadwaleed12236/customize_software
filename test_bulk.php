
require 'vendor/autoload.php';
\ = require_once 'bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\ = App\Models\User::first();
auth()->login(\);

\ = Illuminate\Http\Request::create('/products/bulk-assign', 'GET');
\ = new App\Http\Controllers\ProductController();
\ = \->bulkAssignView(\);
echo 'Response status: ' . (is_object(\) ? get_class(\) : gettype(\)) . PHP_EOL;
try {
    \ = \->render();
    echo 'Rendered successfully! HTML length: ' . strlen(\) . PHP_EOL;
} catch (\Exception \) {
    echo 'Render error: ' . \->getMessage() . PHP_EOL;
}
