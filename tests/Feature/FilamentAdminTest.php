<?php

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Widgets\StatsOverview;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ReceiptPrinter;
use Database\Seeders\ProductCatalogSeeder;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Pages\Auth\Login;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_category_resource_creates_updates_and_deletes_categories(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Minuman',
                'slug' => 'minuman',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::query()->where('slug', 'minuman')->firstOrFail();
        $this->assertModelExists($category);

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm([
                'name' => 'Minuman Dingin',
                'slug' => 'minuman-dingin',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($category);
    }

    public function test_product_resource_creates_updates_and_deletes_products(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'category_id' => $category->id,
                'slug' => 'espresso',
                'name' => 'Espresso',
                'price' => 12000,
                'stock' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->where('slug', 'espresso')->firstOrFail();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $category->id,
            'price' => 12000,
            'stock' => 0,
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'category_id' => $category->id,
                'slug' => 'espresso',
                'name' => 'Double Espresso',
                'price' => 15000,
                'stock' => 8,
                'image' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Double Espresso',
            'price' => 15000,
            'stock' => 8,
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($product);
    }

    public function test_product_table_searches_sorts_and_filters_by_category(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $coffee = Category::factory()->create();
        $tea = Category::factory()->create();
        $americano = Product::factory()->for($coffee)->create([
            'name' => 'Americano',
            'slug' => 'americano',
            'stock' => 5,
        ]);
        $greenTea = Product::factory()->for($tea)->create([
            'name' => 'Green Tea',
            'slug' => 'green-tea',
            'stock' => 6,
        ]);

        $table = Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords([$americano, $greenTea])
            ->searchTable('Americano')
            ->assertCanSeeTableRecords([$americano])
            ->assertCanNotSeeTableRecords([$greenTea])
            ->searchTable(null)
            ->sortTable('name', 'asc')
            ->assertCanSeeTableRecords([$americano, $greenTea], inOrder: true)
            ->filterTable('category', $tea)
            ->assertCanSeeTableRecords([$greenTea])
            ->assertCanNotSeeTableRecords([$americano]);

        $stockColumn = $table->instance()->getTable()->getColumn('stock');

        $this->assertSame('danger', $stockColumn->record($americano)->getColor($americano->stock));
        $this->assertSame('success', $stockColumn->record($greenTea)->getColor($greenTea->stock));
    }

    public function test_admin_dashboard_displays_database_statistics(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();
        Category::factory()->create();
        Product::factory()->for($category)->create(['stock' => 5]);
        Product::factory()->for($category)->create(['stock' => 6]);

        $this->get('/admin')->assertOk();
        $this->assertContains(StatsOverview::class, Filament::getWidgets());

        Livewire::test(StatsOverview::class)
            ->assertSee('Total Kategori')
            ->assertSee('Total Produk')
            ->assertSee('Stok Menipis')
            ->assertSee('Kategori produk terdaftar')
            ->assertSee('Keseluruhan item aktif')
            ->assertSee('Perlu segera dilakukan restock')
            ->assertSee('2')
            ->assertSee('1');
    }

    public function test_admin_dashboard_redirects_guests_to_login(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_regular_users_cannot_log_in_to_or_access_the_admin_panel(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_filament_user_can_be_promoted_and_log_in_as_an_admin(): void
    {
        $this->artisan('make:filament-user', [
            '--name' => 'Filament Administrator',
            '--email' => 'filament-admin@example.test',
            '--password' => 'temporary-test-password',
        ])->assertSuccessful();

        $user = User::query()->where('email', 'filament-admin@example.test')->firstOrFail();

        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('temporary-test-password', $user->password));

        $this->artisan('app:grant-filament-admin', [
            'email' => $user->email,
        ])->assertSuccessful();

        $user->refresh();
        $this->assertTrue($user->is_admin);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'temporary-test-password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk();
    }

    public function test_admin_grant_command_fails_for_an_unknown_user(): void
    {
        $this->artisan('app:grant-filament-admin', [
            'email' => 'missing@example.test',
        ])->assertFailed();
    }

    public function test_filament_product_updates_change_new_checkout_prices_but_preserve_order_snapshots(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'category_id' => $category->id,
                'slug' => 'sample-coffee',
                'name' => 'Sample Coffee',
                'price' => 12000,
                'stock' => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get(route('pos'))
            ->assertOk()
            ->assertSee('Sample Coffee')
            ->assertSee('Rp12.000');

        $this->mock(ReceiptPrinter::class, function (MockInterface $mock): void {
            $mock->shouldReceive('print')->twice();
        });

        $oldOrderResponse = $this->postJson(route('checkout.store'), [
            'items' => [
                ['product_id' => 'sample-coffee', 'quantity' => 1],
            ],
        ]);
        $oldOrderResponse->assertCreated();

        $oldOrder = Order::query()->where('order_id', $oldOrderResponse->json('order_id'))->firstOrFail();
        $oldOrderItem = $oldOrder->items()->firstOrFail();
        $product = Product::query()->where('slug', 'sample-coffee')->firstOrFail();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'category_id' => $category->id,
                'slug' => 'sample-coffee',
                'name' => 'Sample Coffee Special',
                'price' => 15000,
                'stock' => 8,
                'image' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('pos'))
            ->assertOk()
            ->assertSee('Sample Coffee Special')
            ->assertSee('Rp15.000');

        $newOrderResponse = $this->postJson(route('checkout.store'), [
            'items' => [
                ['product_id' => 'sample-coffee', 'quantity' => 1],
            ],
        ]);
        $newOrderResponse->assertCreated();

        $newOrder = Order::query()->where('order_id', $newOrderResponse->json('order_id'))->firstOrFail();
        $newOrderItem = $newOrder->items()->firstOrFail();
        $oldOrderItem->refresh();

        $this->assertSame('Sample Coffee', $oldOrderItem->product_name);
        $this->assertSame(12000, $oldOrderItem->unit_price);
        $this->assertSame('Sample Coffee Special', $newOrderItem->product_name);
        $this->assertSame(15000, $newOrderItem->unit_price);
    }

    public function test_catalog_seeder_imports_existing_menu_without_duplicates(): void
    {
        $this->seed(ProductCatalogSeeder::class);
        $this->seed(ProductCatalogSeeder::class);

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseCount('products', 3);
        $this->assertDatabaseHas('products', [
            'slug' => 'kopi-susu',
            'name' => 'Kopi Susu',
            'price' => 18000,
            'stock' => 0,
        ]);
    }
}
