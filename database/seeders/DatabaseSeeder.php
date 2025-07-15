<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Support\MorphHelper;
use App\Models\User;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Inquiry;

// Listings & Related
use App\Models\ListingRelated\Listing;
use App\Models\ListingRelated\Location;
use App\Models\ListingRelated\LeaseDocument;
use App\Models\ListingRelated\LeaseTermsAndConditions;
use App\Models\ListingRelated\OtherDetailRelated\OtherDetail;
use App\Models\ListingRelated\OtherDetailRelated\AvailabilityInfo;
use App\Models\ListingRelated\OtherDetailRelated\TenantUsePolicy;

// IndLot
use App\Models\ListingRelated\IndLotListing;
use App\Models\ListingRelated\IndLotLeaseRates;
use App\Models\ListingRelated\IndLotListingPropertyDetails;
use App\Models\ListingRelated\IndLotTurnoverConditions;

// Warehouse
use App\Models\ListingRelated\WarehouseListing;
use App\Models\ListingRelated\WarehouseSpecs;
use App\Models\ListingRelated\WarehouseListingPropDetails;
use App\Models\ListingRelated\WarehouseTurnoverConditions;
use App\Models\ListingRelated\WarehouseLeaseRates;

// CommLot
use App\Models\ListingRelated\CommLotListing;
use App\Models\ListingRelated\CommLotListingPropertyDetails;
use App\Models\ListingRelated\CommLotTurnoverConditions;

// Retail Office
use App\Models\ListingRelated\RetailOfficeListing;
use App\Models\ListingRelated\RetailOfficeListingPropertyDetails;
use App\Models\ListingRelated\RetailOfficeTurnoverConditions;
use App\Models\ListingRelated\RetailOfficeBuildingSpecs;
use App\Models\ListingRelated\RetailOfficeOtherDetailExtn;

// Office Space
use App\Models\ListingRelated\OfficeSpaceListing;
use App\Models\ListingRelated\OfficeSpecs;
use App\Models\ListingRelated\OfficeListingPropertyDetails;
use App\Models\ListingRelated\OfficeTurnoverConditions;
use App\Models\ListingRelated\OfficeLeaseTermsAndConditionsExtn;
use App\Models\ListingRelated\OfficeOtherDetailExtn;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Account::factory()->count(7)->create();

        $agentAccounts = Account::factory()
            ->count(7)
            ->state(['role' => 'agent'])
            ->create();

        $viewerAccounts = Account::factory()
            ->count(8)
            ->state(['role' => 'viewer'])
            ->create();

        $contacts = Contact::factory()->count(15)->create();

        // 🧱 IndLot Listings
        $indlots = IndLotListing::factory()
            ->has(IndLotLeaseRates::factory())
            ->has(IndLotListingPropertyDetails::factory())
            ->has(IndLotTurnoverConditions::factory())
            ->count(10)
            ->create();

        $indlots->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(IndLotListing::class),
            'account_id' => $agentAccounts->random()->id,
            'custom_listable_id' => $item->custom_id
        ]));

        // 🏢 Warehouse Listings
        $warehouses = WarehouseListing::factory()
            ->has(WarehouseSpecs::factory())
            ->has(WarehouseListingPropDetails::factory())
            ->has(WarehouseTurnoverConditions::factory())
            ->has(WarehouseLeaseRates::factory())
            ->count(10)
            ->create();

        $warehouses->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(WarehouseListing::class),
            'account_id' => $agentAccounts->random()->id,
            'custom_listable_id' => $item->custom_id
        ]));

        // 🧭 CommLot Listings
        $commlots = CommLotListing::factory()
            ->has(CommLotListingPropertyDetails::factory())
            ->has(CommLotTurnoverConditions::factory())
            ->count(10)
            ->create();

        $commlots->each(fn($item) => Listing::factory()->create([
            'listable_id' => $item->id,
            'listable_type' => MorphHelper::getMorphAlias(CommLotListing::class),
            'account_id' => $agentAccounts->random()->id,
            'custom_listable_id' => $item->custom_id
        ]));

        // 🏬 Retail Office Listings with Dual FK extensions
        $retails = RetailOfficeListing::factory()->count(10)->create();

        $retails->each(function ($retail) use ($agentAccounts) {
            $listing = Listing::factory()->create([
                'listable_id' => $retail->id,
                'listable_type' => MorphHelper::getMorphAlias(RetailOfficeListing::class),
                'account_id' => $agentAccounts->random()->id,
                'custom_listable_id' => $retail->custom_id,
            ]);

            $otherDetail = OtherDetail::factory()->create([
                'listing_id' => $listing->id,
            ]);

            RetailOfficeOtherDetailExtn::factory()->create([
                'retail_office_listing_id' => $retail->id,
                'other_detail_id' => $otherDetail->id,
            ]);

            RetailOfficeListingPropertyDetails::factory()->create([
                'retail_office_listing_id' => $retail->id,
            ]);

            RetailOfficeTurnoverConditions::factory()->create([
                'retail_office_listing_id' => $retail->id,
            ]);

            RetailOfficeBuildingSpecs::factory()->create([
                'retail_office_listing_id' => $retail->id,
            ]);
        });

        // 🏙️ Office Space Listings with Dual FK extensions
        $offices = OfficeSpaceListing::factory()
            ->has(OfficeSpecs::factory())
            ->has(OfficeListingPropertyDetails::factory())
            ->has(OfficeTurnoverConditions::factory())
            ->count(10)
            ->create();

        $offices->each(function ($office) use ($agentAccounts) {
            $listing = Listing::factory()->create([
                'listable_id' => $office->id,
                'listable_type' => MorphHelper::getMorphAlias(OfficeSpaceListing::class),
                'account_id' => $agentAccounts->random()->id,
                'custom_listable_id' => $office->custom_id,
            ]);

            $otherDetail = OtherDetail::factory()->create([
                'listing_id' => $listing->id,
            ]);

            OfficeOtherDetailExtn::factory()->create([
                'office_space_listing_id' => $office->id,
                'other_detail_id' => $otherDetail->id,
            ]);

            OfficeLeaseTermsAndConditionsExtn::factory()->create([
                'office_space_listing_id' => $office->id,
            ]);
        });

        // 🔗 Related Records for All Listings
        Listing::all()->each(function ($listing) use ($contacts, $viewerAccounts) {
            $listing->contacts()->attach($contacts->random(rand(1, 2))->pluck('id'), [
                'company' => fake()->company(),
            ]);

            foreach ($viewerAccounts->random(rand(1, 2)) as $viewer) {
                Inquiry::factory()->create([
                    'account_id' => $viewer->id,
                    'listing_id' => $listing->id,
                    'message' => fake()->sentence(),
                ]);
            }

            LeaseDocument::factory()->create(['listing_id' => $listing->id]);
            LeaseTermsAndConditions::factory()->create(['listing_id' => $listing->id]);
            Location::factory()->create(['listing_id' => $listing->id]);

            // Add optional other detail components if needed
            if (!OtherDetail::where('listing_id', $listing->id)->exists()) {
                $otherDetail = OtherDetail::factory()->create(['listing_id' => $listing->id]);

                AvailabilityInfo::factory()->create(['other_detail_id' => $otherDetail->id]);
                TenantUsePolicy::factory()->create(['other_detail_id' => $otherDetail->id]);
            }
        });
    }
}
