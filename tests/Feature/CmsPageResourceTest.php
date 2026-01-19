<?php

use App\Enums\UserRole;
use App\Filament\Resources\CmsPages\Pages\CreateCmsPage;
use App\Filament\Resources\CmsPages\Pages\EditCmsPage;
use App\Filament\Resources\CmsPages\Pages\ListCmsPages;
use App\Models\CmsPage;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    if (! Role::where('name', UserRole::Admin->value)->exists()) {
        Role::create(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->create();
    $this->admin->assignRole(UserRole::Admin->value);
});

describe('List CMS Pages', function () {
    test('admin can access the list page', function () {
        actingAs($this->admin);

        Livewire::test(ListCmsPages::class)
            ->assertOk();
    });

    test('admin can see CMS pages in the table', function () {
        $pages = CmsPage::factory()->count(3)->create();

        actingAs($this->admin);

        Livewire::test(ListCmsPages::class)
            ->assertCanSeeTableRecords($pages);
    });

    test('admin can search pages by title', function () {
        $page1 = CmsPage::factory()->create(['title' => 'Términos y Condiciones']);
        $page2 = CmsPage::factory()->create(['title' => 'Preguntas Frecuentes']);

        actingAs($this->admin);

        Livewire::test(ListCmsPages::class)
            ->assertCanSeeTableRecords([$page1, $page2])
            ->searchTable('Términos')
            ->assertCanSeeTableRecords([$page1])
            ->assertCanNotSeeTableRecords([$page2]);
    });

    test('admin can filter pages by publication status', function () {
        $published = CmsPage::factory()->published()->create();
        $draft = CmsPage::factory()->draft()->create();

        actingAs($this->admin);

        Livewire::test(ListCmsPages::class)
            ->assertCanSeeTableRecords([$published, $draft])
            ->filterTable('is_published', true)
            ->assertCanSeeTableRecords([$published])
            ->assertCanNotSeeTableRecords([$draft]);
    });

    test('guests cannot access the list page', function () {
        $this->get('/admin/cms-pages')
            ->assertRedirect();
    });
});

describe('Create CMS Page', function () {
    test('admin can access the create page', function () {
        actingAs($this->admin);

        Livewire::test(CreateCmsPage::class)
            ->assertOk();
    });

    test('admin can create a CMS page', function () {
        actingAs($this->admin);

        $newPage = CmsPage::factory()->make();

        Livewire::test(CreateCmsPage::class)
            ->fillForm([
                'title' => $newPage->title,
                'slug' => $newPage->slug,
                'content' => $newPage->content,
                'meta_title' => 'Meta título de prueba',
                'meta_description' => 'Meta descripción de prueba',
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas(CmsPage::class, [
            'title' => $newPage->title,
            'slug' => $newPage->slug,
            'is_published' => true,
            'last_edited_by' => $this->admin->id,
        ]);
    });

    test('admin can create a FAQ page with sections', function () {
        actingAs($this->admin);

        Livewire::test(CreateCmsPage::class)
            ->fillForm([
                'title' => 'FAQ Test',
                'slug' => 'faq-test',
                'content' => '',
                'sections' => [
                    ['question' => '¿Pregunta 1?', 'answer' => 'Respuesta 1'],
                    ['question' => '¿Pregunta 2?', 'answer' => 'Respuesta 2'],
                ],
                'is_published' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = CmsPage::where('slug', 'faq-test')->first();

        expect($page)->not->toBeNull()
            ->and($page->sections)->toHaveCount(2)
            ->and($page->sections[0]['question'])->toBe('¿Pregunta 1?');
    });

    test('title is required', function () {
        actingAs($this->admin);

        Livewire::test(CreateCmsPage::class)
            ->fillForm([
                'title' => null,
                'slug' => 'test-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required']);
    });

    test('slug is required', function () {
        actingAs($this->admin);

        Livewire::test(CreateCmsPage::class)
            ->fillForm([
                'title' => 'Test Title',
                'slug' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'required']);
    });

    test('slug must be unique', function () {
        CmsPage::factory()->create(['slug' => 'existing-slug']);

        actingAs($this->admin);

        Livewire::test(CreateCmsPage::class)
            ->fillForm([
                'title' => 'Test Title',
                'slug' => 'existing-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    });

    test('slug is auto-generated from title on create', function () {
        actingAs($this->admin);

        Livewire::test(CreateCmsPage::class)
            ->fillForm([
                'title' => 'Mi Nueva Página de Prueba',
            ])
            ->assertSchemaStateSet([
                'slug' => 'mi-nueva-pagina-de-prueba',
            ]);
    });
});

describe('Edit CMS Page', function () {
    test('admin can access the edit page', function () {
        $page = CmsPage::factory()->create();

        actingAs($this->admin);

        Livewire::test(EditCmsPage::class, ['record' => $page->id])
            ->assertOk();
    });

    test('edit page loads with correct data', function () {
        $page = CmsPage::factory()->published()->create();

        actingAs($this->admin);

        Livewire::test(EditCmsPage::class, ['record' => $page->id])
            ->assertSchemaStateSet([
                'title' => $page->title,
                'slug' => $page->slug,
                'is_published' => true,
            ]);
    });

    test('admin can update a CMS page', function () {
        $page = CmsPage::factory()->create();

        actingAs($this->admin);

        Livewire::test(EditCmsPage::class, ['record' => $page->id])
            ->fillForm([
                'title' => 'Updated Title',
                'content' => 'Updated content',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        assertDatabaseHas(CmsPage::class, [
            'id' => $page->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'last_edited_by' => $this->admin->id,
        ]);
    });

    test('admin can update FAQ sections', function () {
        $page = CmsPage::factory()->create(['sections' => null]);

        actingAs($this->admin);

        Livewire::test(EditCmsPage::class, ['record' => $page->id])
            ->fillForm([
                'sections' => [
                    ['question' => 'Nueva pregunta?', 'answer' => 'Nueva respuesta'],
                    ['question' => 'Segunda pregunta?', 'answer' => 'Segunda respuesta'],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $page->refresh();

        expect($page->sections)->toHaveCount(2)
            ->and($page->sections[0]['question'])->toBe('Nueva pregunta?')
            ->and($page->sections[1]['question'])->toBe('Segunda pregunta?');
    });

    test('admin can publish a draft page', function () {
        $page = CmsPage::factory()->draft()->create();

        actingAs($this->admin);

        Livewire::test(EditCmsPage::class, ['record' => $page->id])
            ->fillForm([
                'is_published' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        assertDatabaseHas(CmsPage::class, [
            'id' => $page->id,
            'is_published' => true,
        ]);
    });

    test('admin can delete a CMS page', function () {
        $page = CmsPage::factory()->create();

        actingAs($this->admin);

        Livewire::test(EditCmsPage::class, ['record' => $page->id])
            ->callAction(\Filament\Actions\DeleteAction::class);

        assertDatabaseMissing(CmsPage::class, [
            'id' => $page->id,
        ]);
    });

    test('slug must be unique when editing', function () {
        $existingPage = CmsPage::factory()->create(['slug' => 'existing-slug']);
        $pageToEdit = CmsPage::factory()->create(['slug' => 'my-slug']);

        actingAs($this->admin);

        Livewire::test(EditCmsPage::class, ['record' => $pageToEdit->id])
            ->fillForm([
                'slug' => 'existing-slug',
            ])
            ->call('save')
            ->assertHasFormErrors(['slug' => 'unique']);
    });

    test('can keep the same slug when editing', function () {
        $page = CmsPage::factory()->create(['slug' => 'my-unique-slug']);

        actingAs($this->admin);

        Livewire::test(EditCmsPage::class, ['record' => $page->id])
            ->fillForm([
                'title' => 'Updated Title',
                'slug' => 'my-unique-slug', // Same slug
            ])
            ->call('save')
            ->assertHasNoFormErrors();
    });
});
