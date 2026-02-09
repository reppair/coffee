<?php

use App\Enums\ProductType;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProductActivities;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Models\BulkStock;
use App\Models\Category;
use App\Models\Location;
use App\Models\PackageStock;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->location = Location::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->admin->locations()->attach($this->location);

    $this->actingAs($this->admin);
    Filament::setTenant($this->location);
    Filament::bootCurrentPanel();
});

it('can list products', function () {
    $products = Product::factory()->count(3)->create();

    livewire(ListProducts::class)
        ->assertOk()
        ->assertCanSeeTableRecords($products);
});

it('can search products by name', function () {
    $target = Product::factory()->create(['name' => 'Ethiopian Yirgacheffe']);
    $other = Product::factory()->create(['name' => 'Colombian Supremo']);

    livewire(ListProducts::class)
        ->searchTable('Ethiopian Yirgacheffe')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can sort products by name', function () {
    $products = Product::factory()->count(3)->create();

    livewire(ListProducts::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($products->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($products->sortByDesc('name'), inOrder: true);
});

it('can render the create page', function () {
    livewire(CreateProduct::class)
        ->assertOk();
});

it('can create a product', function () {
    $category = Category::factory()->create();

    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'New Product',
            'description' => 'A test product',
            'category_id' => $category->id,
            'type' => ProductType::Coffee->value,
            'sku' => 'TEST123',
            'is_active' => true,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(Product::class, [
        'name' => 'New Product',
        'slug' => 'new-product',
        'description' => 'A test product',
        'category_id' => $category->id,
        'type' => ProductType::Coffee->value,
        'sku' => 'TEST123',
        'is_active' => true,
    ]);
});

it('validates required fields on create', function (array $data, array $errors) {
    livewire(CreateProduct::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`type` is required' => [['name' => 'Test', 'type' => null], ['type' => 'required']],
]);

it('can render the view page', function () {
    $product = Product::factory()->create();

    livewire(ViewProduct::class, ['record' => $product->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $product->name,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'type' => $product->type,
            'sku' => $product->sku,
            'is_active' => $product->is_active,
        ]);
});

it('can render the edit page', function () {
    $product = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $product->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $product->name,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'type' => $product->type,
            'sku' => $product->sku,
            'is_active' => $product->is_active,
        ]);
});

it('can update a product', function () {
    $product = Product::factory()->create();
    $category = Category::factory()->create();

    livewire(EditProduct::class, ['record' => $product->id])
        ->fillForm([
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'category_id' => $category->id,
            'type' => ProductType::Tea->value,
            'sku' => 'UPDATED456',
            'is_active' => false,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Product::class, [
        'id' => $product->id,
        'name' => 'Updated Product',
        'slug' => 'updated-product',
        'description' => 'Updated description',
        'category_id' => $category->id,
        'type' => ProductType::Tea->value,
        'sku' => 'UPDATED456',
        'is_active' => false,
    ]);
});

it('can soft delete a product with zero-quantity stock', function () {
    $product = Product::factory()->create();
    BulkStock::factory()->empty()->for($product)->create();
    PackageStock::factory()->empty()->for($product)->create();

    livewire(ViewProduct::class, ['record' => $product->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertSoftDeleted(Product::class, ['id' => $product->id]);
    assertDatabaseHas(Product::class, ['id' => $product->id]);
});

it('cannot delete a product with active bulk stock', function () {
    $product = Product::factory()->create();
    BulkStock::factory()->for($product)->create(['quantity_grams' => 500]);

    livewire(ViewProduct::class, ['record' => $product->id])
        ->assertActionDisabled(DeleteAction::class);

    assertDatabaseHas(Product::class, ['id' => $product->id]);
});

it('cannot delete a product with active package stock', function () {
    $product = Product::factory()->create();
    PackageStock::factory()->for($product)->create(['quantity' => 5]);

    livewire(ViewProduct::class, ['record' => $product->id])
        ->assertActionDisabled(DeleteAction::class);

    assertDatabaseHas(Product::class, ['id' => $product->id]);
});

it('denies staff access to product pages', function (string $page, array $params) {
    $staff = User::factory()->staff()->create();
    $staff->locations()->attach($this->location);

    $this->actingAs($staff);

    livewire($page, $params)
        ->assertForbidden();
})->with([
    'list' => [ListProducts::class, []],
    'create' => [CreateProduct::class, []],
    'view' => fn () => [ViewProduct::class, ['record' => Product::factory()->create()->id]],
    'edit' => fn () => [EditProduct::class, ['record' => Product::factory()->create()->id]],
]);

it('defaults is_active to true on create', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Default Active Product',
            'type' => ProductType::Coffee->value,
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Product::class, [
        'name' => 'Default Active Product',
        'is_active' => true,
    ]);
});

it('can render the activities page', function () {
    $product = Product::factory()->create();

    livewire(ListProductActivities::class, ['record' => $product->id])
        ->assertOk();
});

it('does not allow restoring activities', function () {
    $product = Product::factory()->create();

    $component = livewire(ListProductActivities::class, ['record' => $product->id]);

    expect($component->instance()->canRestoreActivity())->toBeFalse();
});

it('has expected table actions', function (string $action) {
    livewire(ListProducts::class)
        ->assertTableActionExists($action);
})->with([
    'view' => ViewAction::class,
    'edit' => EditAction::class,
    'delete' => DeleteAction::class,
    'restore' => RestoreAction::class,
]);

it('can access edit page from table edit action', function () {
    $product = Product::factory()->create();

    livewire(ListProducts::class)
        ->callTableAction(EditAction::class, $product);
});

it('can restore a soft-deleted product via view page', function () {
    $product = Product::factory()->create();
    $product->delete();

    livewire(ViewProduct::class, ['record' => $product->id])
        ->callAction(RestoreAction::class)
        ->assertNotified();

    expect($product->fresh()->trashed())->toBeFalse();
});

it('can force delete a soft-deleted product via view page', function () {
    $product = Product::factory()->create();
    $product->delete();

    livewire(ViewProduct::class, ['record' => $product->id])
        ->callAction(ForceDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    expect(Product::withTrashed()->find($product->id))->toBeNull();
});

it('cannot force delete a product with active inventory', function () {
    $product = Product::factory()->create();
    BulkStock::factory()->for($product)->create(['quantity_grams' => 500]);
    $product->delete();

    livewire(ViewProduct::class, ['record' => $product->id])
        ->assertActionDisabled(ForceDeleteAction::class);
});

it('hides soft-deleted products by default', function () {
    $active = Product::factory()->create();
    $trashed = Product::factory()->create();
    $trashed->delete();

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$trashed])
        ->assertTableFilterExists('trashed');
});

it('has expected view page header actions', function (string $action) {
    $product = Product::factory()->create();

    livewire(ViewProduct::class, ['record' => $product->id])
        ->assertActionExists($action);
})->with([
    'activities' => 'activities',
    'edit' => EditAction::class,
    'delete' => DeleteAction::class,
]);

it('shows force delete and restore actions only on trashed products', function () {
    $product = Product::factory()->create();

    livewire(ViewProduct::class, ['record' => $product->id])
        ->assertActionHidden(ForceDeleteAction::class)
        ->assertActionHidden(RestoreAction::class)
        ->assertActionVisible(DeleteAction::class);
});

it('shows force delete and restore on trashed product view page', function () {
    $product = Product::factory()->create();
    $product->delete();

    livewire(ViewProduct::class, ['record' => $product->id])
        ->assertActionVisible(ForceDeleteAction::class)
        ->assertActionVisible(RestoreAction::class)
        ->assertActionHidden(DeleteAction::class);
});

it('edit page has no header actions', function (string $action) {
    $product = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $product->id])
        ->assertActionDoesNotExist($action);
})->with([
    'delete' => DeleteAction::class,
    'force delete' => ForceDeleteAction::class,
    'restore' => RestoreAction::class,
]);

it('can filter products by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $product1 = Product::factory()->for($category1, 'category')->create();
    $product2 = Product::factory()->for($category2, 'category')->create();

    livewire(ListProducts::class)
        ->filterTable('category_id', $category1->id)
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2]);
});

it('can filter products by type', function () {
    $coffee = Product::factory()->coffee()->create();
    $tea = Product::factory()->tea()->create();

    livewire(ListProducts::class)
        ->filterTable('type', ProductType::Coffee->value)
        ->assertCanSeeTableRecords([$coffee])
        ->assertCanNotSeeTableRecords([$tea]);
});

it('can filter products by active status', function () {
    $active = Product::factory()->create(['is_active' => true]);
    $inactive = Product::factory()->inactive()->create();

    livewire(ListProducts::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$inactive]);
});

it('category column has url configured', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    livewire(ListProducts::class)
        ->assertTableColumnExists('category.name', function (TextColumn $column) use ($category): bool {
            return $column->getUrl() === CategoryResource::getUrl('view', ['record' => $category->id]);
        }, $product);
});

it('category column returns null url when product has no category', function () {
    $product = Product::factory()->create(['category_id' => null]);

    livewire(ListProducts::class)
        ->assertTableColumnExists('category.name', function (TextColumn $column): bool {
            return $column->getUrl() === null;
        }, $product);
});
