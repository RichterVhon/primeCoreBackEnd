<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Contact;
use App\Models\Inquiry;
use App\Support\MorphHelper;
use Illuminate\Database\Seeder;
use App\Models\ListingRelated\Listing;
use App\Models\ListingRelated\Location;
use App\Models\ListingRelated\OfficeSpecs;
use App\Models\ListingRelated\IndLotListing;
use App\Models\ListingRelated\LeaseDocument;
use App\Models\ListingRelated\CommLotListing;

use App\Models\ListingRelated\WarehouseSpecs;


use App\Models\ListingRelated\IndLotLeaseRates;
use App\Models\ListingRelated\WarehouseListing;
use App\Models\ListingRelated\OfficeSpaceListing;
use App\Models\ListingRelated\RetailOfficeListing;
use App\Models\ListingRelated\WarehouseLeaseRates;
use App\Models\ListingRelated\OfficeOtherDetailExtn;

use App\Models\ListingRelated\LeaseTermsAndConditions;
use App\Models\ListingRelated\IndLotTurnoverConditions;
use App\Models\ListingRelated\OfficeTurnoverConditions;
use App\Models\ListingRelated\CommLotTurnoverConditions;
use App\Models\ListingRelated\RetailOfficeBuildingSpecs;
use App\Models\ListingRelated\RetailOfficeOtherDetailExtn;
use App\Models\ListingRelated\WarehouseListingPropDetails;
use App\Models\ListingRelated\WarehouseTurnoverConditions;
use App\Models\ListingRelated\IndLotListingPropertyDetails;
use App\Models\ListingRelated\OfficeListingPropertyDetails;
use App\Models\ListingRelated\CommLotListingPropertyDetails;
use App\Models\ListingRelated\OtherDetailRelated\OtherDetail;
use App\Models\ListingRelated\RetailOfficeTurnoverConditions;
use Database\Factories\ListingRelated\WarehouseListingFactory;
use App\Models\ListingRelated\OfficeLeaseTermsAndConditionsExtn;
use App\Models\ListingRelated\OtherDetailRelated\TenantUsePolicy;
use App\Models\ListingRelated\RetailOfficeListingPropertyDetails;
use App\Models\ListingRelated\OtherDetailRelated\AvailabilityInfo;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */

        Account::factory()->count(7)->create();
        $agentAccounts = Account::factory()
            ->count(7)
            ->state(['role' => 'agent'])
            ->create();
        //$agentaccounts = Account::where('role', 'agent')->get();



        $indlots = IndLotListing::factory()
            ->has(IndLotLeaseRates::factory())
            ->has(IndLotListingPropertyDetails::factory())
            ->has(IndLotTurnoverConditions::factory())
            ->count(10)
            ->create();

        $warehouses = WarehouseListing::factory()
            ->has(WarehouseTurnoverConditions::factory())
            ->has(WarehouseListingPropDetails::factory())
            ->has(WarehouseLeaseRates::factory())
            ->has(WarehouseSpecs::factory())
            ->count(10)
            ->create();

        $commlots = CommLotListing::factory()
            ->has(CommLotTurnoverConditions::factory())
            ->has(CommLotListingPropertyDetails::factory())
            ->count(10)
            ->create();


        $retails = RetailOfficeListing::factory()
            //->has(RetailOfficeListingPropertyDetails::factory())
            //->has(RetailOfficeTurnoverConditions::factory())
            //->has(RetailOfficeBuildingSpecs::factory())
            // ->has(RetailOfficeOtherDetailExtn::factory())

            ->count(10)
            ->create();

        $offices = OfficeSpaceListing::factory()
            ->has(OfficeSpecs::factory())
            ->has(OfficeTurnoverConditions::factory())
            ->has(OfficeListingPropertyDetails::factory())
            // ->has(OfficeLeaseTermsAndConditionsExtn::factory()) 
            // ->has(OfficeOtherDetailExtn::factory())

            ->count(10)
            ->create();

        $indlots->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(IndLotListing::class),
            'account_id' => $agentAccounts->random()->id,
            'custom_listable_id' => $item->custom_id
        ]));

        $warehouses->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(WarehouseListing::class),
            'account_id' => $agentAccounts->random()->id,
            'custom_listable_id' => $item->custom_id
        ]));

        $commlots->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(CommLotListing::class),
            'account_id' => $agentAccounts->random()->id,
            'custom_listable_id' => $item->custom_id
        ]));

        $retails->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(RetailOfficeListing::class),
            'account_id' => $agentAccounts->random()->id,
            'custom_listable_id' => $item->custom_id
        ]));

        $offices->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(OfficeSpaceListing::class),
            'account_id' => $agentAccounts->random()->id,
            'custom_listable_id' => $item->custom_id
        ]));

        $contacts = Contact::factory()->count(15)->create();


        // Attach contacts to accounts with pivot data
        $agentAccounts->each(function ($agentAccounts) use ($contacts) {
            $agentAccounts->contacts()->attach(
                $contacts->random(rand(2, 5))->pluck('id')->toArray(),
                ['company_name' => fake()->company()] // 'relationship_type' => 'client']
            );
        });


        Listing::all()->each(function ($listing) use ($contacts) {
            $assignedContacts = $contacts->random(rand(1, 2));

            foreach ($assignedContacts as $contact) {
                $listing->contacts()->attach($contact->id, [
                    'company' => fake()->company(),
                ]);
            }
        });

        $viewerAccounts = Account::factory()
            ->count(8)
            ->state(['role' => 'viewer'])
            ->create();

        Listing::all()->each(function ($listing) use ($viewerAccounts) {

            $assignedViewers = $viewerAccounts->random(rand(1, 2));
            foreach ($assignedViewers as $viewer) {
                Inquiry::factory()->create([
                    'account_id' => $viewer->id,
                    'listing_id' => $listing->id,
                    'message' => fake()->sentence(),
                ]);
            }
            // Attach an OtherDetail
            $otherDetail = OtherDetail::factory()->create([
                'listing_id' => $listing->id,
            ]);

            // Availability Info 
            AvailabilityInfo::factory()->create([
                'other_detail_id' => $otherDetail->id,
            ]);

            TenantUsePolicy::factory()->create([
                'other_detail_id' => $otherDetail->id,
            ]);

            // Lease Terms for this listing
            LeaseTermsAndConditions::factory()->create([
                'listing_id' => $listing->id,
            ]);

            // Lease Document metadata
            LeaseDocument::factory()->create([
                'listing_id' => $listing->id,
            ]);

            Location::factory()->create([
                'listing_id' => $listing->id,
            ]);
        });
    }
}
