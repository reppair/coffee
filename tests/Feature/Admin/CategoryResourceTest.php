<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Pages\ListCategoryActivities;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Categories\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->location = Location::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->admin->locations()->attach($this->location);

    $this->actingAs($this->admin);
    Filament::setTenant($this->location);
    Filament::bootCurrentPanel();
});

it('can list categories', function () {
    $categories = Category::factory()->count(3)->create();

    livewire(ListCategories::class)
        ->assertOk()
        ->assertCanSeeTableRecords($categories);
});

it('can search categories by name', function () {
    $target = Category::factory()->create(['name' => 'Single Origin']);
    $other = Category::factory()->create(['name' => 'Blends']);

    livewire(ListCategories::class)
        ->searchTable('Single Origin')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can sort categories by name', function () {
    $categories = Category::factory()->count(3)->create();

    livewire(ListCategories::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($categories->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($categories->sortByDesc('name'), inOrder: true);
});

it('can render the create page', function () {
    livewire(CreateCategory::class)
        ->assertOk();
});

it('can create a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'New Category',
            'description' => 'A test description',
            'is_active' => true,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(Category::class, [
        'name' => 'New Category',
        'slug' => 'new-category',
        'description' => 'A test description',
        'is_active' => true,
    ]);
});

it('validates required fields on create', function (array $data, array $errors) {
    livewire(CreateCategory::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
]);

it('can render the view page', function () {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, ['record' => $category->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $category->name,
            'description' => $category->description,
            'is_active' => $category->is_active,
        ]);
});

it('can render the edit page', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $category->name,
            'description' => $category->description,
            'is_active' => $category->is_active,
        ]);
});

it('can update a category', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->id])
        ->fillForm([
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'is_active' => false,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Category::class, [
        'id' => $category->id,
        'name' => 'Updated Name',
        'slug' => 'updated-name',
        'description' => 'Updated description',
        'is_active' => false,
    ]);
});

it('can delete a category without products', function () {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, ['record' => $category->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Category::class, ['id' => $category->id]);
});

it('cannot delete a category with products', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->create();

    livewire(ViewCategory::class, ['record' => $category->id])
        ->assertActionDisabled(DeleteAction::class);

    assertDatabaseHas(Category::class, ['id' => $category->id]);
});

it('denies staff access to category pages', function (string $page, array $params) {
    $staff = User::factory()->staff()->create();
    $staff->locations()->attach($this->location);

    $this->actingAs($staff);

    livewire($page, $params)
        ->assertForbidden();
})->with([
    'list' => [ListCategories::class, []],
    'create' => [CreateCategory::class, []],
    'view' => fn () => [ViewCategory::class, ['record' => Category::factory()->create()->id]],
    'edit' => fn () => [EditCategory::class, ['record' => Category::factory()->create()->id]],
]);

it('defaults is_active to true on create', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Default Active',
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Category::class, [
        'name' => 'Default Active',
        'is_active' => true,
    ]);
});

it('can render the activities page', function () {
    $category = Category::factory()->create();

    livewire(ListCategoryActivities::class, ['record' => $category->id])
        ->assertOk();
});

it('does not allow restoring activities', function () {
    $category = Category::factory()->create();

    $component = livewire(ListCategoryActivities::class, ['record' => $category->id]);

    expect($component->instance()->canRestoreActivity())->toBeFalse();
});

it('has expected table actions', function (string $action) {
    livewire(ListCategories::class)
        ->assertTableActionExists($action);
})->with([
    'view' => ViewAction::class,
    'edit' => EditAction::class,
    'delete' => DeleteAction::class,
]);

it('can access edit page from table edit action', function () {
    $category = Category::factory()->create();

    livewire(ListCategories::class)
        ->callTableAction(EditAction::class, $category);
});

it('has expected view page header actions', function (string $action) {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, ['record' => $category->id])
        ->assertActionExists($action);
})->with([
    'activities' => 'activities',
    'edit' => EditAction::class,
    'delete' => DeleteAction::class,
]);

it('renders products relation manager on view page', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->for($category)->create();

    livewire(ViewCategory::class, ['record' => $category->id])
        ->assertSeeLivewire(ProductsRelationManager::class);
});

it('shows category products in relation manager', function () {
    $category = Category::factory()->create();
    $products = Product::factory()->count(3)->for($category)->create();

    livewire(ProductsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertCanSeeTableRecords($products);
});

it('does not show other category products in relation manager', function () {
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();

    $ownProducts = Product::factory()->count(2)->for($category)->create();
    $otherProducts = Product::factory()->count(2)->for($otherCategory)->create();

    livewire(ProductsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertCanSeeTableRecords($ownProducts)
        ->assertCanNotSeeTableRecords($otherProducts);
});

it('does not show relation manager on edit page', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->id])
        ->assertDontSeeLivewire(ProductsRelationManager::class);
});

it('has view action on relation manager rows', function () {
    $category = Category::factory()->create();

    livewire(ProductsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertTableActionExists(ViewAction::class);
});

it('view action in relation manager links to product view page', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    $component = livewire(ProductsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ]);

    $table = $component->instance()->getTable();
    $viewAction = collect($table->getRecordActions())->first(fn ($action) => $action instanceof ViewAction);

    expect($viewAction)->not->toBeNull();

    $url = $viewAction->getUrl($product);
    $expectedUrl = ProductResource::getUrl('view', ['record' => $product->id]);

    expect($url)->toBe($expectedUrl);
});

it('products in relation manager are sorted by category_sort_order', function () {
    $category = Category::factory()->create();

    $product1 = Product::factory()->for($category)->create(['name' => 'First']);
    $product2 = Product::factory()->for($category)->create(['name' => 'Second']);
    $product3 = Product::factory()->for($category)->create(['name' => 'Third']);

    expect($product1->fresh()->category_sort_order)->toBe(1)
        ->and($product2->fresh()->category_sort_order)->toBe(2)
        ->and($product3->fresh()->category_sort_order)->toBe(3);

    livewire(ProductsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertCanSeeTableRecords([$product1, $product2, $product3], inOrder: true);
});

it('relation manager has reorderable configured', function () {
    $category = Category::factory()->create();

    $component = livewire(ProductsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ]);

    $table = $component->instance()->getTable();

    expect($table->getReorderColumn())->toBe('category_sort_order');
});

it('relation manager does not have create or associate actions', function () {
    $category = Category::factory()->create();

    $component = livewire(ProductsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ]);

    $table = $component->instance()->getTable();
    $headerActions = $table->getHeaderActions();

    expect($headerActions)->toBeEmpty();
});

it('relation manager does not have edit or delete actions', function () {
    $category = Category::factory()->create();

    livewire(ProductsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertTableActionDoesNotExist(EditAction::class)
        ->assertTableActionDoesNotExist(DeleteAction::class);
});
