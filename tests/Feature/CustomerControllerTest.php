<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_displays_customers_index_page()
    {
        // Arrange
        Customer::factory()->count(3)->create();

        // Act
        $response = $this->get(route('customers.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('customer.index');
        $response->assertViewHas('customers');
        $response->assertSee('Customers');
    }

    /** @test */
    public function it_displays_create_customer_form()
    {
        // Act
        $response = $this->get(route('customers.create'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('customer.create');
    }

    /** @test */
    public function it_stores_a_new_customer_successfully()
    {
        // Arrange
        $customerData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'date_of_birth' => $this->faker->date(),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'account_number' => $this->faker->bankAccountNumber,
            'about' => $this->faker->text(200),
        ];

        // Act
        $response = $this->post(route('customers.store'), $customerData);

        // Assert
        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success', 'Customer created successfully.');

        $this->assertDatabaseHas('customers', [
            'first_name' => $customerData['first_name'],
            'last_name' => $customerData['last_name'],
            'email' => $customerData['email'],
        ]);
    }

    /** @test */
    public function it_stores_customer_with_image_upload()
    {
        // Arrange
        $image = UploadedFile::fake()->image('customer.jpg');
        $customerData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'date_of_birth' => $this->faker->date(),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'account_number' => $this->faker->bankAccountNumber,
            'about' => $this->faker->text(200),
            'image' => $image,
        ];

        // Act
        $response = $this->post(route('customers.store'), $customerData);

        // Assert
        $response->assertRedirect(route('customers.index'));

        $customer = Customer::where('email', $customerData['email'])->first();
        $this->assertNotNull($customer);
        $this->assertStringContainsString('/uploads/', $customer->image);

        // Check if file was stored
        $imagePath = str_replace('/uploads/', '', $customer->image);
        $this->assertTrue(Storage::disk('public')->exists($imagePath));
    }

    /** @test */
    public function it_validates_required_fields_when_storing_customer()
    {
        // Act
        $response = $this->post(route('customers.store'), []);

        // Assert
        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'email',
            'phone',
            'account_number'
        ]);
    }

    /** @test */
    public function it_validates_unique_email_when_storing_customer()
    {
        // Arrange
        $existingCustomer = Customer::factory()->create();

        $customerData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'date_of_birth' => $this->faker->date(),
            'email' => $existingCustomer->email, // Duplicate email
            'phone' => $this->faker->phoneNumber,
            'account_number' => $this->faker->bankAccountNumber,
            'about' => $this->faker->text(200),
        ];

        // Act
        $response = $this->post(route('customers.store'), $customerData);

        // Assert
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_displays_customer_details()
    {
        // Arrange
        $customer = Customer::factory()->create();

        // Act
        $response = $this->get(route('customers.show', $customer->id));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('customer.detail');
        $response->assertViewHas('customer', $customer);
        $response->assertSee($customer->first_name);
        $response->assertSee($customer->last_name);
        $response->assertSee($customer->email);
    }

    /** @test */
    public function it_returns_404_for_non_existent_customer()
    {
        // Act
        $response = $this->get(route('customers.show', 999));

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function it_deletes_customer_successfully()
    {
        // Arrange
        $customer = Customer::factory()->create();

        // Act
        $response = $this->delete(route('customers.destroy', $customer->id));

        // Assert
        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success', 'Customer deleted successfully.');
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    /** @test */
    public function it_deletes_customer_image_when_deleting_customer()
    {
        // Arrange
        $image = UploadedFile::fake()->image('customer.jpg');
        Storage::disk('public')->put('test-image.jpg', $image);

        $customer = Customer::factory()->create([
            'image' => '/uploads/test-image.jpg'
        ]);

        // Act
        $response = $this->delete(route('customers.destroy', $customer->id));

        // Assert
        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertFalse(Storage::disk('public')->exists('test-image.jpg'));
    }

    /** @test */
    public function it_does_not_delete_default_image_when_deleting_customer()
    {
        // Arrange
        $customer = Customer::factory()->create([
            'image' => '/default-images/ben.png'
        ]);

        // Act
        $response = $this->delete(route('customers.destroy', $customer->id));

        // Assert
        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        // Default image should remain (we're not actually checking file system for default images)
    }

    /** @test */
    public function it_returns_404_when_deleting_non_existent_customer()
    {
        // Act
        $response = $this->delete(route('customers.destroy', 999));

        // Assert
        $response->assertStatus(404);
    }
}
