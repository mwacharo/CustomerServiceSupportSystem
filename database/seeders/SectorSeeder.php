<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    public function run()
    {
        // Agriculture Sector
        $agriculture = Sector::create([
            'code' => '1000',
            'name' => 'AGRICULTURE',
            'parent_id' => null,
        ]);

        // ... (Previous agriculture subsectors remain unchanged)

        // Trade Sector
        $trade = Sector::create([
            'code' => '2000',
            'name' => 'TRADE',
            'parent_id' => null,
        ]);

        $wholesaleAndRetail = Sector::create([
            'code' => '2100',
            'name' => 'Wholesale and Retail',
            'parent_id' => $trade->id,
        ]);

        Sector::create([
            'code' => '2110',
            'name' => 'Wholesale',
            'parent_id' => $wholesaleAndRetail->id,
        ]);

        Sector::create([
            'code' => '2120',
            'name' => 'Retail',
            'parent_id' => $wholesaleAndRetail->id,
        ]);

        $transport = Sector::create([
            'code' => '2200',
            'name' => 'Transport',
            'parent_id' => $trade->id,
        ]);

        Sector::create([
            'code' => '2210',
            'name' => 'Public service transport',
            'parent_id' => $transport->id,
        ]);

        Sector::create([
            'code' => '2220',
            'name' => 'Purchase of motorvehicle accessories',
            'parent_id' => $transport->id,
        ]);

        Sector::create([
            'code' => '2230',
            'name' => 'Transportation of goods',
            'parent_id' => $transport->id,
        ]);

        $hospitality = Sector::create([
            'code' => '2300',
            'name' => 'Hospitality',
            'parent_id' => $trade->id,
        ]);

        Sector::create([
            'code' => '2310',
            'name' => 'Accomodation, restaurants, conference facilities, event planning & outside catering, (wedding and others)',
            'parent_id' => $hospitality->id,
        ]);

        Sector::create([
            'code' => '2320',
            'name' => 'Schools and kindergartens',
            'parent_id' => $hospitality->id,
        ]);

        Sector::create([
            'code' => '2330',
            'name' => 'Medical clinics and equipment',
            'parent_id' => $hospitality->id,
        ]);

        $foreignTrade = Sector::create([
            'code' => '2400',
            'name' => 'Foreign Trade',
            'parent_id' => $trade->id,
        ]);

        Sector::create([
            'code' => '2410',
            'name' => 'Import',
            'parent_id' => $foreignTrade->id,
        ]);

        Sector::create([
            'code' => '2420',
            'name' => 'Export',
            'parent_id' => $foreignTrade->id,
        ]);

        // Manufacturing and Servicing Industries Sector
        $manufacturing = Sector::create([
            'code' => '3000',
            'name' => 'MANUFACTURING AND SERVICING INDUSTRIES',
            'parent_id' => null,
        ]);

        $cottageIndustry = Sector::create([
            'code' => '3100',
            'name' => 'Cottage Industry',
            'parent_id' => $manufacturing->id,
        ]);

        Sector::create([
            'code' => '3110',
            'name' => 'Jua kali Industry',
            'parent_id' => $cottageIndustry->id,
        ]);

        Sector::create([
            'code' => '3120',
            'name' => 'Small scale Agricultural Produce processing',
            'parent_id' => $cottageIndustry->id,
        ]);

        Sector::create([
            'code' => '3130',
            'name' => 'Dressmaking Industry',
            'parent_id' => $cottageIndustry->id,
        ]);

        Sector::create([
            'code' => '3140',
            'name' => 'Leather tanning',
            'parent_id' => $cottageIndustry->id,
        ]);

        Sector::create([
            'code' => '3150',
            'name' => 'Carving and handcrafts',
            'parent_id' => $cottageIndustry->id,
        ]);

        $servicingIndustry = Sector::create([
            'code' => '3200',
            'name' => 'Servicing Industry',
            'parent_id' => $manufacturing->id,
        ]);

        Sector::create([
            'code' => '3210',
            'name' => 'Motorvehicle repairs',
            'parent_id' => $servicingIndustry->id,
        ]);

        Sector::create([
            'code' => '3220',
            'name' => 'Professional services such as Barber shops',
            'parent_id' => $servicingIndustry->id,
        ]);

        Sector::create([
            'code' => '3230',
            'name' => 'Working capital for learning institutions, churches & business enterprises',
            'parent_id' => $servicingIndustry->id,
        ]);

        Sector::create([
            'code' => '3240',
            'name' => 'Promotion of local tourism',
            'parent_id' => $servicingIndustry->id,
        ]);

        $ict = Sector::create([
            'code' => '3300',
            'name' => 'Information, Communication and Technology',
            'parent_id' => $manufacturing->id,
        ]);

        Sector::create([
            'code' => '3310',
            'name' => 'Computer services and Internet',
            'parent_id' => $ict->id,
        ]);

        Sector::create([
            'code' => '3320',
            'name' => 'Computer software and hardware',
            'parent_id' => $ict->id,
        ]);

        Sector::create([
            'code' => '3330',
            'name' => 'Telecommunication Equipment',
            'parent_id' => $ict->id,
        ]);

        // Education Sector
        $education = Sector::create([
            'code' => '4000',
            'name' => 'EDUCATION',
            'parent_id' => null,
        ]);

        $educationServices = Sector::create([
            'code' => '4100',
            'name' => 'Education and related services',
            'parent_id' => $education->id,
        ]);

        Sector::create([
            'code' => '4110',
            'name' => 'School fees for primary and secondary schools including accomodation',
            'parent_id' => $educationServices->id,
        ]);

        Sector::create([
            'code' => '4120',
            'name' => 'College fees, University fees, training fees, seminar fees',
            'parent_id' => $educationServices->id,
        ]);

        Sector::create([
            'code' => '4130',
            'name' => 'Research and scientific activities etc',
            'parent_id' => $educationServices->id,
        ]);

        // Human Health Sector
        $humanHealth = Sector::create([
            'code' => '5000',
            'name' => 'HUMAN HEALTH',
            'parent_id' => null,
        ]);

        $healthServices = Sector::create([
            'code' => '5100',
            'name' => 'Human health and related services',
            'parent_id' => $humanHealth->id,
        ]);

        Sector::create([
            'code' => '5110',
            'name' => 'Medical Bills, purchase of medicine',
            'parent_id' => $healthServices->id,
        ]);

        Sector::create([
            'code' => '5120',
            'name' => 'Maternity Bills and expenses',
            'parent_id' => $healthServices->id,
        ]);

        // Land and Housing Sector
        $landAndHousing = Sector::create([
            'code' => '6000',
            'name' => 'LAND AND HOUSING',
            'parent_id' => null,
        ]);

        $land = Sector::create([
            'code' => '6100',
            'name' => 'Land',
            'parent_id' => $landAndHousing->id,
        ]);

        Sector::create([
            'code' => '6110',
            'name' => 'Purchase of plots',
            'parent_id' => $land->id,
        ]);

        Sector::create([
            'code' => '6120',
            'name' => 'Land purchase services such as surveying and valuation',
            'parent_id' => $land->id,
        ]);

        $housing = Sector::create([
            'code' => '6200',
            'name' => 'Housing',
            'parent_id' => $landAndHousing->id,
        ]);

        Sector::create([
            'code' => '6210',
            'name' => 'Construction of multiple residential buildings',
            'parent_id' => $housing->id,
        ]);

        Sector::create([
            'code' => '6220',
            'name' => 'Construction of commercial buildings',
            'parent_id' => $housing->id,
        ]);

        Sector::create([
            'code' => '6230',
            'name' => 'Construction of single residential dwelling units',
            'parent_id' => $housing->id,
        ]);

        Sector::create([
            'code' => '6240',
            'name' => 'Renovations of the buildings',
            'parent_id' => $housing->id,
        ]);

        // Finance, Investments and Insurance Sector
        $finance = Sector::create([
            'code' => '7000',
            'name' => 'FINANCE, INVESTMENTS AND INSURANCE',
            'parent_id' => null,
        ]);

        $microfinance = Sector::create([
            'code' => '7100',
            'name' => 'Microfinance',
            'parent_id' => $finance->id,
        ]);

        Sector::create([
            'code' => '7110',
            'name' => 'Payment to microfinance loans',
            'parent_id' => $microfinance->id,
        ]);

        $commercialBanks = Sector::create([
            'code' => '7200',
            'name' => 'Commercial Banks',
            'parent_id' => $finance->id,
        ]);

        Sector::create([
            'code' => '7210',
            'name' => 'Payment to Commercial bank loans',
            'parent_id' => $commercialBanks->id,
        ]);

        $mortgageFinance = Sector::create([
            'code' => '7300',
            'name' => 'Mortgage Finance',
            'parent_id' => $finance->id,
        ]);

        Sector::create([
            'code' => '7310',
            'name' => 'Purchase of residential property/payments to mortgage loans in other financial institutions',
            'parent_id' => $mortgageFinance->id,
        ]);

        $insurance = Sector::create([
            'code' => '7400',
            'name' => 'Insurance',
            'parent_id' => $finance->id,
        ]);

        Sector::create([
            'code' => '7410',
            'name' => 'Payment to insurance policies',
            'parent_id' => $insurance->id,
        ]);

        $investments = Sector::create([
            'code' => '7500',
            'name' => 'Investments',
            'parent_id' => $finance->id,
        ]);

        Sector::create([
            'code' => '7510',
            'name' => 'Buying of Sacco shares',
            'parent_id' => $investments->id,
        ]);

        Sector::create([
            'code' => '7520',
            'name' => 'purchase of quote shares, unquoted shares, treasury bills & bonds, commercial papers, unit trusts and other quoted public funds',
            'parent_id' => $investments->id,
        ]);

        Sector::create([
            'code' => '7530',
            'name' => 'Paying personal debts to non-registered institutions',
            'parent_id' => $investments->id,
        ]);

        // Consumption and Social Services Sector
        $consumptionAndSocial = Sector::create([
            'code' => '8000',
            'name' => 'CONSUMPTION AND SOCIAL SERVICES',
            'parent_id' => null,
        ]);

        $utilities1 = Sector::create([
            'code' => '8100',
            'name' => 'Utilities',
            'parent_id' => $consumptionAndSocial->id,
        ]);

        Sector::create([
            'code' => '8110',
            'name' => 'Expenses incurred relating to car and electronic repairs, bills like electricity, sewer, water, telephone, decoder, personal debts to family members and friends etc.',
            'parent_id' => $utilities1->id,
        ]);

        $utilities2 = Sector::create([
            'code' => '8200',
            'name' => 'Utilities',
            'parent_id' => $consumptionAndSocial->id,
        ]);

        Sector::create([
            'code' => '8210',
            'name' => 'Household necessities like food, beverages and basic household products.',
            'parent_id' => $utilities2->id,
        ]);

        $consumerDurables = Sector::create([
            'code' => '8300',
            'name' => 'Consumer Durables',
            'parent_id' => $consumptionAndSocial->id,
        ]);

        Sector::create([
            'code' => '8310',
            'name' => 'Goods that do not wear out quickly like automobiles(cars), books, household(home appliances, consumer electronics, furniture, tools etc) sports equipment, jewellery, toys etc',
            'parent_id' => $consumerDurables->id,
        ]);

        $socialAndCommunal = Sector::create([
            'code' => '8400',
            'name' => 'Social and communal expenses',
            'parent_id' => $consumptionAndSocial->id,
        ]);

        Sector::create([
            'code' => '8410',
            'name' => 'Burial expenses, wedding expenses, rites of passage expenses.',
            'parent_id' => $socialAndCommunal->id,
        ]);
    }
}