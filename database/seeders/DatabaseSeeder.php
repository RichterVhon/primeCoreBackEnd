<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Contact;
use App\Models\Inquiry;
use App\Support\MorphHelper;
use Illuminate\Database\Seeder;
use App\Models\ListingRelated\IndLotLeaseRates;
use App\Models\ListingRelated\Listing;
use App\Models\ListingRelated\Location;
use App\Models\ListingRelated\IndLotListing;
use App\Models\ListingRelated\LeaseDocument;
use App\Models\ListingRelated\CommLotListing;

use App\Models\ListingRelated\WarehouseSpecs;

use App\Models\ListingRelated\IndLotLeaseRates;

use App\Models\ListingRelated\WarehouseListing;
use App\Models\ListingRelated\OfficeSpaceListing;
use App\Models\ListingRelated\RetailOfficeListing;
use App\Models\ListingRelated\WarehouseLeaseRates;
use App\Models\ListingRelated\LeaseTermsAndConditions;
use App\Models\ListingRelated\IndLotTurnoverConditions;

use App\Models\ListingRelated\CommLotTurnoverConditions;
use App\Models\ListingRelated\WarehouseListingPropDetails;
use App\Models\ListingRelated\WarehouseTurnoverConditions;
use App\Models\ListingRelated\IndLotListingPropertyDetails;
use App\Models\ListingRelated\CommLotListingPropertyDetails;

use App\Models\ListingRelated\IndLotListingPropertyDetails;

use App\Models\ListingRelated\OtherDetailRelated\OtherDetail;
use Database\Factories\ListingRelated\WarehouseListingFactory;
use App\Models\ListingRelated\OtherDetailRelated\TenantUsePolicy;
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
       

        $retails = RetailOfficeListing::factory()->count(10)->create();
        $offices = OfficeSpaceListing::factory()->count(10)->create();

        $indlots->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(IndLotListing::class),
            'account_id' => $agentAccounts->random()->id,
        ]));

        $warehouses->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(WarehouseListing::class),
            'account_id' => $agentAccounts->random()->id,
        ]));

        $commlots->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(CommLotListing::class),
            'account_id' => $agentAccounts->random()->id,
        ]));

        $retails->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(RetailOfficeListing::class),
            'account_id' => $agentAccounts->random()->id,
        ]));

        $offices->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(OfficeSpaceListing::class),
            'account_id' => $agentAccounts->random()->id,
        ]));

        $contacts = Contact::factory()->count(15)->create();

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
