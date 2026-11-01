<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DeliveryCharge;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\TenantPrescription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantPrescriptionSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensurePrescriptionSvgs();

        $tenants = Tenant::all();
        $adminUser = User::where('email', 'john@medicarepharma.com')->first() ?? User::first();

        foreach ($tenants as $tenant) {
            DB::transaction(function () use ($tenant, $adminUser) {
                $products = $this->seedPharmacyCatalog($tenant);
                $customer = Customer::where('tenant_id', $tenant->id)->first();
                $deliveryCharge = DeliveryCharge::where('tenant_id', $tenant->id)->where('status', true)->first();

                $this->seedPrescriptions($tenant, $customer, $adminUser, $products, $deliveryCharge);
                $this->seedCartCheckoutWithRxOrder($tenant, $customer, $products, $deliveryCharge);
            });
        }
    }

    private function ensurePrescriptionSvgs(): void
    {
        $dir = storage_path('app/public/prescriptions');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $svg2Path = $dir . '/rx_demo_2.svg';
        if (!file_exists($svg2Path)) {
            $svg2 = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1100" width="100%" height="100%">
  <defs>
    <style>
      .bg { fill: #fbfdfe; }
      .header-title { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: bold; fill: #0369a1; }
      .header-sub { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; fill: #475569; }
      .hospital-name { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; fill: #0284c7; }
      .divider { stroke: #0284c7; stroke-width: 2.5; }
      .thin-divider { stroke: #cbd5e1; stroke-width: 1; }
      .label { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 600; fill: #64748b; }
      .value { font-family: 'Courier New', Courier, monospace; font-size: 14px; font-weight: bold; fill: #0f172a; }
      .rx-symbol { font-family: 'Georgia', serif; font-size: 42px; font-weight: bold; fill: #0369a1; font-style: italic; }
      .med-name { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; fill: #0f172a; }
      .med-dose { font-family: 'Courier New', Courier, monospace; font-size: 14px; fill: #334155; }
      .stamp-circle { stroke: #0284c7; stroke-width: 2; fill: none; stroke-dasharray: 4, 2; }
      .stamp-text { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; fill: #0284c7; font-weight: bold; text-anchor: middle; }
    </style>
  </defs>

  <rect width="800" height="1100" class="bg"/>
  <rect x="20" y="20" width="760" height="1060" fill="none" stroke="#e0f2fe" stroke-width="1.5" rx="8"/>
  <text x="400" y="580" font-family="Georgia" font-size="160" font-weight="bold" fill="#f0f9ff" text-anchor="middle" transform="rotate(-30 400 580)">Rx</text>

  <g transform="translate(50, 60)">
    <text x="0" y="24" class="header-title">DR. FARHANA HUQ</text>
    <text x="0" y="46" class="header-sub">MBBS (DMC), DCH (BSMMU), FCPS (Pediatrics)</text>
    <text x="0" y="64" class="header-sub">Assistant Professor &amp; Specialist Child Physician</text>
    <text x="0" y="82" class="header-sub" font-weight="bold">BMDC Reg. No: A-58421</text>

    <text x="700" y="24" class="hospital-name" text-anchor="end">SQUARE HOSPITALS LTD.</text>
    <text x="700" y="44" class="header-sub" text-anchor="end">18/F Bir Uttam Qazi Nuruzzaman Sarak</text>
    <text x="700" y="62" class="header-sub" text-anchor="end">West Panthapath, Dhaka 1205</text>
    <text x="700" y="80" class="header-sub" text-anchor="end">Hotline: 10616 | +880 1713-377775</text>
  </g>

  <line x1="50" y1="165" x2="750" y2="165" class="divider"/>

  <g transform="translate(50, 180)">
    <rect x="0" y="0" width="700" height="42" fill="#f0f9ff" rx="6" stroke="#bae6fd"/>
    <text x="15" y="26" class="label">Patient Name:</text>
    <text x="110" y="26" class="value">Master Aayan Kabir</text>
    <text x="330" y="26" class="label">Age:</text>
    <text x="365" y="26" class="value">4 Yrs</text>
    <text x="440" y="26" class="label">Sex:</text>
    <text x="475" y="26" class="value">Male</text>
    <text x="530" y="26" class="label">Wt:</text>
    <text x="560" y="26" class="value">16.2 kg</text>
    <text x="635" y="26" class="label">Date:</text>
    <text x="675" y="26" class="value" text-anchor="end">Today</text>
  </g>

  <g transform="translate(50, 245)">
    <line x1="170" y1="0" x2="170" y2="720" class="thin-divider"/>
    <text x="0" y="20" class="label" fill="#0369a1" font-weight="bold">CLINICAL NOTES</text>
    <text x="0" y="45" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">C/C: High fever 3d</text>
    <text x="0" y="65" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">Cough &amp; Runny nose</text>
    <text x="0" y="85" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">Temp: 102.4 °F</text>
    <text x="0" y="105" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">Chest: B/L wheeze</text>
    <text x="0" y="125" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">Throat: Congested</text>
    <text x="0" y="175" class="label" fill="#0369a1" font-weight="bold">ADVICE</text>
    <text x="0" y="200" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#64748b">• Lukewarm sponge</text>
    <text x="0" y="220" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#64748b">• Plenty of fluids</text>
    <text x="0" y="240" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#64748b">• Avoid cold food</text>
    <text x="0" y="260" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#64748b">• Follow up in 5d</text>
  </g>

  <g transform="translate(240, 245)">
    <text x="0" y="32" class="rx-symbol">℞</text>
    <g transform="translate(45, 20)">
      <text x="0" y="16" class="med-name">1. Syp. Napa 120mg/5ml (Paracetamol)</text>
      <text x="15" y="36" class="med-dose">1.5 teaspoonful (7.5ml) -- 3 to 4 times daily</text>
      <text x="15" y="54" class="header-sub">Only if temperature exceeds 100°F (5 days)</text>
    </g>
    <g transform="translate(45, 95)">
      <text x="0" y="16" class="med-name">2. Syp. Zimax / Azithromycin 200mg/5ml</text>
      <text x="15" y="36" class="med-dose">1 teaspoonful (5ml) -- 1+0+0 (Once daily)</text>
      <text x="15" y="54" class="header-sub">1 hour before meals for 5 days continuous</text>
    </g>
    <g transform="translate(45, 170)">
      <text x="0" y="16" class="med-name">3. Syp. Montene 4mg / Montelukast oral susp.</text>
      <text x="15" y="36" class="med-dose">1 teaspoonful -- 0+0+1 (At bedtime)</text>
      <text x="15" y="54" class="header-sub">Continue for 14 days regularly</text>
    </g>
    <g transform="translate(45, 245)">
      <text x="0" y="16" class="med-name">4. Syp. Tofen 1mg/5ml (Ketotifen)</text>
      <text x="15" y="36" class="med-dose">1/2 teaspoonful (2.5ml) -- 1+0+1 (After meal)</text>
      <text x="15" y="54" class="header-sub">For 7 days</text>
    </g>
    <g transform="translate(45, 320)">
      <text x="0" y="16" class="med-name">5. Solokid Nasal Drops 0.05%</text>
      <text x="15" y="36" class="med-dose">1 drop in each nostril -- 3 times daily</text>
      <text x="15" y="54" class="header-sub">Before feeding for 3 to 5 days</text>
    </g>
  </g>

  <g transform="translate(560, 920)">
    <rect x="-30" y="-30" width="180" height="60" rx="6" class="stamp-circle"/>
    <text x="60" y="-8" class="stamp-text">SQUARE HOSPITALS LTD.</text>
    <text x="60" y="8" class="stamp-text" font-size="10px">PEDIATRIC OUTPATIENT</text>
    <text x="60" y="20" class="stamp-text" font-size="9px">VERIFIED DIGITAL RX</text>
    <path d="M-20,60 C20,30 40,75 80,45 C110,20 130,55 170,40" fill="none" stroke="#0369a1" stroke-width="2.2" stroke-linecap="round"/>
    <line x1="-30" y1="70" x2="180" y2="70" stroke="#94a3b8" stroke-width="1"/>
    <text x="75" y="86" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569" text-anchor="middle">Dr. Farhana Huq</text>
    <text x="75" y="100" font-family="'Helvetica Neue', sans-serif" font-size="10px" fill="#94a3b8" text-anchor="middle">Specialist Pediatrician</text>
  </g>

  <line x1="50" y1="1035" x2="750" y2="1035" class="thin-divider"/>
  <text x="400" y="1055" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#94a3b8" text-anchor="middle">
    Notice: Dispense strictly according to registered medical practitioner's directions. Store in a cool dry place away from sunlight.
  </text>
</svg>
SVG;
            file_put_contents($svg2Path, $svg2);
        }

        $svg3Path = $dir . '/rx_demo_3.svg';
        if (!file_exists($svg3Path)) {
            $svg3 = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1100" width="100%" height="100%">
  <defs>
    <style>
      .bg { fill: #fcfbf9; }
      .header-title { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 23px; font-weight: bold; fill: #1e1b4b; }
      .header-sub { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; fill: #475569; }
      .hospital-name { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; fill: #4338ca; }
      .divider { stroke: #4338ca; stroke-width: 2.5; }
      .thin-divider { stroke: #cbd5e1; stroke-width: 1; }
      .label { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 600; fill: #64748b; }
      .value { font-family: 'Courier New', Courier, monospace; font-size: 14px; font-weight: bold; fill: #0f172a; }
      .rx-symbol { font-family: 'Georgia', serif; font-size: 42px; font-weight: bold; fill: #4338ca; font-style: italic; }
      .med-name { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; fill: #0f172a; }
      .med-dose { font-family: 'Courier New', Courier, monospace; font-size: 14px; fill: #334155; }
      .stamp-circle { stroke: #4338ca; stroke-width: 2; fill: none; stroke-dasharray: 4, 2; }
      .stamp-text { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; fill: #4338ca; font-weight: bold; text-anchor: middle; }
    </style>
  </defs>

  <rect width="800" height="1100" class="bg"/>
  <rect x="20" y="20" width="760" height="1060" fill="none" stroke="#e2e8f0" stroke-width="1.5" rx="8"/>
  <text x="400" y="580" font-family="Georgia" font-size="160" font-weight="bold" fill="#eef2ff" text-anchor="middle" transform="rotate(-30 400 580)">Rx</text>

  <g transform="translate(50, 60)">
    <text x="0" y="24" class="header-title">PROF. DR. S. R. CHOWDHURY</text>
    <text x="0" y="46" class="header-sub">MBBS, MS (Orthopedics), FACS (USA), FICS</text>
    <text x="0" y="64" class="header-sub">Senior Consultant Orthopedic &amp; Joint Replacement Surgeon</text>
    <text x="0" y="82" class="header-sub" font-weight="bold">BMDC Reg. No: A-21950</text>

    <text x="700" y="24" class="hospital-name" text-anchor="end">APOLLO IMPERIAL HOSPITAL</text>
    <text x="700" y="44" class="header-sub" text-anchor="end">Zakir Hossain Road, Pahartali</text>
    <text x="700" y="62" class="header-sub" text-anchor="end">Chattogram 4202, Bangladesh</text>
    <text x="700" y="80" class="header-sub" text-anchor="end">Helpline: 09612-247247</text>
  </g>

  <line x1="50" y1="165" x2="750" y2="165" class="divider"/>

  <g transform="translate(50, 180)">
    <rect x="0" y="0" width="700" height="42" fill="#f5f3ff" rx="6" stroke="#ddd6fe"/>
    <text x="15" y="26" class="label">Patient Name:</text>
    <text x="110" y="26" class="value">Begum Rokeya Sultana</text>
    <text x="330" y="26" class="label">Age:</text>
    <text x="365" y="26" class="value">58 Yrs</text>
    <text x="440" y="26" class="label">Sex:</text>
    <text x="475" y="26" class="value">Female</text>
    <text x="530" y="26" class="label">Wt:</text>
    <text x="560" y="26" class="value">68 kg</text>
    <text x="635" y="26" class="label">Date:</text>
    <text x="675" y="26" class="value" text-anchor="end">Today</text>
  </g>

  <g transform="translate(50, 245)">
    <line x1="170" y1="0" x2="170" y2="720" class="thin-divider"/>
    <text x="0" y="20" class="label" fill="#4338ca" font-weight="bold">DIAGNOSIS</text>
    <text x="0" y="45" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">Post-Op Right TKR</text>
    <text x="0" y="65" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">(Knee Replacement)</text>
    <text x="0" y="85" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">2nd Wk Follow-up</text>
    <text x="0" y="105" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569">Mild knee effusion</text>
    <text x="0" y="155" class="label" fill="#4338ca" font-weight="bold">INSTRUCTIONS</text>
    <text x="0" y="180" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#64748b">• Ice compression 15m</text>
    <text x="0" y="200" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#64748b">• Quad sets exercise</text>
    <text x="0" y="220" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#64748b">• Walker assist walk</text>
    <text x="0" y="240" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#64748b">• Stitch check in 10d</text>
  </g>

  <g transform="translate(240, 245)">
    <text x="0" y="32" class="rx-symbol">℞</text>
    <g transform="translate(45, 20)">
      <text x="0" y="16" class="med-name">1. Tab. Torax 10mg (Ketorolac Tromethamine)</text>
      <text x="15" y="36" class="med-dose">1 tablet -- 1+0+1 (After meal) for 5 days only</text>
      <text x="15" y="54" class="header-sub">Do not exceed 5 days without consultation</text>
    </g>
    <g transform="translate(45, 95)">
      <text x="0" y="16" class="med-name">2. Cap. Maxpro 20mg (Esomeprazole)</text>
      <text x="15" y="36" class="med-dose">1 capsule -- 1+0+1 (30 min before meal)</text>
      <text x="15" y="54" class="header-sub">Continue for 14 days</text>
    </g>
    <g transform="translate(45, 170)">
      <text x="0" y="16" class="med-name">3. Tab. Calbo-D (Calcium 500mg + Vit D3)</text>
      <text x="15" y="36" class="med-dose">1 tablet -- 0+0+1 (After dinner)</text>
      <text x="15" y="54" class="header-sub">Continue for 30 days regularly</text>
    </g>
    <g transform="translate(45, 245)">
      <text x="0" y="16" class="med-name">4. Tab. Cef-3 400mg (Cefixime Trihydrate)</text>
      <text x="15" y="36" class="med-dose">1 tablet -- 1+0+0 (After breakfast)</text>
      <text x="15" y="54" class="header-sub">Take for 7 days complete course</text>
    </g>
    <g transform="translate(45, 320)">
      <text x="0" y="16" class="med-name">5. Tab. Myoril 4mg (Thiocolchicoside)</text>
      <text x="15" y="36" class="med-dose">1 tablet -- 1+0+1 (After meal)</text>
      <text x="15" y="54" class="header-sub">Muscle relaxant -- for 7 days</text>
    </g>
  </g>

  <g transform="translate(560, 920)">
    <rect x="-30" y="-30" width="180" height="60" rx="6" class="stamp-circle"/>
    <text x="60" y="-8" class="stamp-text">APOLLO IMPERIAL HOSPITAL</text>
    <text x="60" y="8" class="stamp-text" font-size="10px">ORTHOPEDIC SURGERY</text>
    <text x="60" y="20" class="stamp-text" font-size="9px">VERIFIED DIGITAL RX</text>
    <path d="M-20,60 C30,20 60,80 100,30 C130,10 150,60 175,45" fill="none" stroke="#4338ca" stroke-width="2.2" stroke-linecap="round"/>
    <line x1="-30" y1="70" x2="180" y2="70" stroke="#94a3b8" stroke-width="1"/>
    <text x="75" y="86" font-family="'Helvetica Neue', sans-serif" font-size="12px" fill="#475569" text-anchor="middle">Prof. Dr. S. R. Chowdhury</text>
    <text x="75" y="100" font-family="'Helvetica Neue', sans-serif" font-size="10px" fill="#94a3b8" text-anchor="middle">Senior Consultant Orthopedic Surgeon</text>
  </g>

  <line x1="50" y1="1035" x2="750" y2="1035" class="thin-divider"/>
  <text x="400" y="1055" font-family="'Helvetica Neue', sans-serif" font-size="11px" fill="#94a3b8" text-anchor="middle">
    Notice: Dispense strictly according to registered medical practitioner's directions. Store in a cool dry place away from sunlight.
  </text>
</svg>
SVG;
            file_put_contents($svg3Path, $svg3);
        }
    }

    private function seedPharmacyCatalog(Tenant $tenant): array
    {
        $rxCategory = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'prescription-medicines'],
            [
                'parent_id' => null,
                'name' => 'Prescription Medicines',
                'position' => 1,
                'status' => true,
                'meta_title' => 'Prescription Medicines Online',
                'h1_heading' => 'Prescription Medicines',
                'meta_description' => 'Order verified prescription medicines online with prompt delivery.',
            ]
        );

        $otcCategory = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'otc-wellness'],
            [
                'parent_id' => null,
                'name' => 'OTC & Daily Healthcare',
                'position' => 2,
                'status' => true,
                'meta_title' => 'OTC & Daily Healthcare',
                'h1_heading' => 'Over The Counter Medicines',
                'meta_description' => 'Everyday over-the-counter pain relief, fever, and wellness essentials.',
            ]
        );

        $squareBrand = Brand::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'square-pharmaceuticals'],
            [
                'name' => 'Square Pharmaceuticals Ltd.',
                'status' => true,
                'meta_title' => 'Square Pharmaceuticals',
                'h1_heading' => 'Square Pharmaceuticals',
                'meta_description' => 'Quality medicines from Square Pharmaceuticals.',
            ]
        );

        $beximcoBrand = Brand::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'beximco-pharma'],
            [
                'name' => 'Beximco Pharmaceuticals Ltd.',
                'status' => true,
                'meta_title' => 'Beximco Pharma',
                'h1_heading' => 'Beximco Pharmaceuticals',
                'meta_description' => 'Trusted healthcare solutions from Beximco Pharma.',
            ]
        );

        $inceptaBrand = Brand::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'incepta-pharmaceuticals'],
            [
                'name' => 'Incepta Pharmaceuticals Ltd.',
                'status' => true,
                'meta_title' => 'Incepta Pharmaceuticals',
                'h1_heading' => 'Incepta Pharmaceuticals',
                'meta_description' => 'Innovative medicines from Incepta.',
            ]
        );

        $items = [
            [
                'name' => 'Napa Extra 500mg/65mg Tablet',
                'slug' => 'napa-extra-500mg-tablet',
                'category_id' => $otcCategory->id,
                'brand_id' => $beximcoBrand->id,
                'requires_prescription' => false,
                'description' => 'Paracetamol 500mg and Caffeine 65mg. Indicated for relief of mild to moderate fever and headache.',
                'sku' => 'NAPA-EX-BOX',
                'price' => 250.00,
                'cost' => 180.00,
                'stock' => 80,
                'variant_title' => 'Box of 100 Tablets (10 Strips)',
            ],
            [
                'name' => 'Seclo 20mg Capsule',
                'slug' => 'seclo-20mg-capsule',
                'category_id' => $otcCategory->id,
                'brand_id' => $squareBrand->id,
                'requires_prescription' => false,
                'description' => 'Omeprazole BP 20mg delayed-release capsule for gastric acidity, heartburn, and GERD relief.',
                'sku' => 'SECLO-20-STRIP',
                'price' => 70.00,
                'cost' => 50.00,
                'stock' => 120,
                'variant_title' => 'Strip of 10 Capsules',
            ],
            [
                'name' => 'Azithrocin 500mg Tablet',
                'slug' => 'azithrocin-500mg-tablet',
                'category_id' => $rxCategory->id,
                'brand_id' => $squareBrand->id,
                'requires_prescription' => true,
                'description' => 'Azithromycin USP 500mg broad spectrum antibiotic for upper and lower respiratory tract infections.',
                'sku' => 'AZITH-500-12',
                'price' => 420.00,
                'cost' => 310.00,
                'stock' => 45,
                'variant_title' => 'Box of 12 Tablets',
            ],
            [
                'name' => 'Montene 10mg Tablet',
                'slug' => 'montene-10mg-tablet',
                'category_id' => $rxCategory->id,
                'brand_id' => $inceptaBrand->id,
                'requires_prescription' => true,
                'description' => 'Montelukast Sodium 10mg leukotriene receptor antagonist for asthma management and allergic rhinitis.',
                'sku' => 'MONT-10-30',
                'price' => 480.00,
                'cost' => 350.00,
                'stock' => 50,
                'variant_title' => 'Box of 30 Tablets',
            ],
            [
                'name' => 'Torax 10mg Tablet',
                'slug' => 'torax-10mg-tablet',
                'category_id' => $rxCategory->id,
                'brand_id' => $squareBrand->id,
                'requires_prescription' => true,
                'description' => 'Ketorolac Tromethamine USP 10mg potent analgesic for short-term management of moderate to severe acute pain.',
                'sku' => 'TORAX-10-20',
                'price' => 300.00,
                'cost' => 220.00,
                'stock' => 30,
                'variant_title' => 'Box of 20 Tablets',
            ],
            [
                'name' => 'Calbo-D 500mg/200IU Tablet',
                'slug' => 'calbo-d-tablet',
                'category_id' => $otcCategory->id,
                'brand_id' => $squareBrand->id,
                'requires_prescription' => false,
                'description' => 'Calcium Carbonate 500mg and Vitamin D3 (Cholecalciferol) 200IU for bone health and osteoporosis prevention.',
                'sku' => 'CALBO-D-30',
                'price' => 210.00,
                'cost' => 150.00,
                'stock' => 70,
                'variant_title' => 'Plastic Bottle of 30 Tablets',
            ],
            [
                'name' => 'Cef-3 400mg Capsule',
                'slug' => 'cef-3-400mg-capsule',
                'category_id' => $rxCategory->id,
                'brand_id' => $squareBrand->id,
                'requires_prescription' => true,
                'description' => 'Cefixime Trihydrate USP 400mg third-generation cephalosporin antibiotic.',
                'sku' => 'CEF3-400-7',
                'price' => 350.00,
                'cost' => 260.00,
                'stock' => 40,
                'variant_title' => 'Box of 7 Capsules',
            ],
        ];

        $createdProducts = [];

        foreach ($items as $item) {
            $product = Product::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $item['slug']],
                [
                    'kind' => 'general',
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'status' => 'active',
                    'category_id' => $item['category_id'],
                    'brand_id' => $item['brand_id'],
                    'requires_prescription' => $item['requires_prescription'],
                    'specs' => ['dosage_form' => 'Oral', 'therapeutic_class' => 'Pharmaceutical'],
                    'seo_title' => $item['name'] . ' Buy Online',
                    'seo_description' => $item['description'],
                ]
            );

            $variant = ProductVariant::firstOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $item['sku']],
                [
                    'product_id' => $product->id,
                    'title' => $item['variant_title'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost'],
                    'compare_at_price' => $item['price'] * 1.1,
                    'mrp' => $item['price'] * 1.15,
                    'track_inventory' => true,
                    'is_default' => true,
                    'position' => 0,
                    'status' => 'active',
                    'options_hash' => md5($item['sku']),
                ]
            );

            $batch = InventoryBatch::firstOrCreate(
                ['variant_id' => $variant->id, 'batch_no' => 'BAT-' . $item['sku']],
                [
                    'tenant_id' => $tenant->id,
                    'expiry_date' => now()->addMonths(18),
                    'quantity' => $item['stock'],
                    'reserved' => 0,
                    'cost_price' => $item['cost'],
                    'received_at' => now()->subDays(10),
                    'status' => 'active',
                ]
            );

            StockMovement::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'variant_id' => $variant->id,
                    'batch_id' => $batch->id,
                    'type' => 'receive',
                ],
                [
                    'qty_delta' => $item['stock'],
                    'reserved_delta' => 0,
                    'reference_type' => 'seeder',
                    'reference_id' => $product->id,
                    'user_id' => $tenant->user_id,
                    'created_at' => now(),
                    'note' => 'Initial medicine inventory for Rx testing',
                ]
            );

            $createdProducts[$item['slug']] = [
                'product' => $product,
                'variant' => $variant,
            ];
        }

        return $createdProducts;
    }

    private function seedPrescriptions(Tenant $tenant, ?Customer $customer, ?User $adminUser, array $products, ?DeliveryCharge $deliveryCharge): void
    {
        // 1. Pending Review: Chronic Diabetes & Hypertension (rx_demo_1.svg)
        TenantPrescription::firstOrCreate(
            ['tenant_id' => $tenant->id, 'prescription_number' => 'RX-2026-001'],
            [
                'customer_id' => $customer?->id,
                'customer_name' => 'Haji Mohammad Ali',
                'customer_phone' => '+880 1711-234567',
                'customer_email' => 'haji.ali@example.com',
                'delivery_address' => 'House 34, Road 11, Dhanmondi',
                'delivery_city' => 'Dhaka',
                'delivery_area' => 'Dhanmondi',
                'patient_notes' => 'Please deliver 1 full month supply of all items written on this doctor slip. Call me before sending delivery person.',
                'prescription_image' => 'prescriptions/rx_demo_1.svg',
                'status' => 'pending_review',
                'created_at' => now()->subMinutes(35),
            ]
        );

        // 2. Pending Review: Pediatric Bronchitis & Cough (rx_demo_2.svg)
        TenantPrescription::firstOrCreate(
            ['tenant_id' => $tenant->id, 'prescription_number' => 'RX-2026-002'],
            [
                'customer_id' => null,
                'customer_name' => 'Tariqul Islam',
                'customer_phone' => '+880 1819-876543',
                'customer_email' => 'tariqul.islam@example.com',
                'delivery_address' => 'Flat 4A, Green Garden Heights, Mirpur DOHS',
                'delivery_city' => 'Dhaka',
                'delivery_area' => 'Mirpur DOHS',
                'patient_notes' => 'Pediatric medicines for my 4 year old son. Please review doctor handwriting carefully and add dosage tags on the bottles.',
                'prescription_image' => 'prescriptions/rx_demo_2.svg',
                'status' => 'pending_review',
                'created_at' => now()->subHours(2),
            ]
        );

        // 3. Pending Review: Post-Op Knee Surgery (rx_demo_3.svg)
        TenantPrescription::firstOrCreate(
            ['tenant_id' => $tenant->id, 'prescription_number' => 'RX-2026-003'],
            [
                'customer_id' => null,
                'customer_name' => 'Begum Rokeya Sultana',
                'customer_phone' => '+880 1912-345678',
                'customer_email' => 'rokeya.sultana@example.com',
                'delivery_address' => 'Holding 18, Road 4, Sector 7, Uttara',
                'delivery_city' => 'Dhaka',
                'delivery_area' => 'Uttara',
                'patient_notes' => 'Post total knee replacement medicines as prescribed by Prof. Chowdhury. Need Torax 10mg, Calbo-D and Cef-3 urgent delivery.',
                'prescription_image' => 'prescriptions/rx_demo_3.svg',
                'status' => 'pending_review',
                'created_at' => now()->subHours(5),
            ]
        );

        // 4. Pending Review: Allergy & Respiratory Refill (rx_demo_1.svg)
        TenantPrescription::firstOrCreate(
            ['tenant_id' => $tenant->id, 'prescription_number' => 'RX-2026-004'],
            [
                'customer_id' => null,
                'customer_name' => 'Mahbubur Rahman',
                'customer_phone' => '+880 1670-112233',
                'customer_email' => 'mahbubur.rahman@example.com',
                'delivery_address' => 'Plot 42, Block C, Bashundhara R/A',
                'delivery_city' => 'Dhaka',
                'delivery_area' => 'Bashundhara',
                'patient_notes' => 'Monthly repeat refill. Cash on Delivery please.',
                'prescription_image' => 'prescriptions/rx_demo_1.svg',
                'status' => 'pending_review',
                'created_at' => now()->subDays(1),
            ]
        );

        // 5. Converted to Order: Already reviewed and created into an Order
        $convertedOrder = Order::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_number' => 'ORD-2026-RX1'],
            [
                'public_id' => (string) Str::uuid(),
                'customer_id' => $customer?->id,
                'status' => 'processing',
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'prescription_status' => 'approved',
                'prescription_image' => 'prescriptions/rx_demo_2.svg',
                'is_direct_prescription' => true,
                'pharmacist_rx_notes' => 'Contacted patient guardian via phone. Verified pediatric dosages: Napa syrup 1.5 tsp TDS, Zimax 1 tsp OD for 5 days. Added instructions label.',
                'rx_reviewed_by_user_id' => $adminUser?->id,
                'rx_reviewed_at' => now()->subDays(1),
                'currency' => 'BDT',
                'subtotal' => 670.00,
                'shipping_charge' => 60.00,
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'total' => 730.00,
                'delivery_charge_id' => $deliveryCharge?->id,
                'shipping_method' => $deliveryCharge?->title ?? 'Inside Dhaka Standard',
                'shipping_name' => 'Sarah Jenkins',
                'shipping_phone' => '+880 1711-223344',
                'shipping_email' => 'sarah.jenkins@example.com',
                'shipping_address' => 'Apartment 4B, Green Lake Residence, Road 11, Block D, Banani',
                'shipping_city' => 'Dhaka',
                'shipping_area' => 'Banani',
                'order_note' => 'Converted from uploaded prescription slip RX-2026-005.',
                'placed_at' => now()->subDays(1),
            ]
        );

        if (isset($products['napa-extra-500mg-tablet']) && isset($products['azithrocin-500mg-tablet'])) {
            OrderProduct::firstOrCreate(
                ['tenant_id' => $tenant->id, 'order_id' => $convertedOrder->id, 'variant_id' => $products['napa-extra-500mg-tablet']['variant']->id],
                [
                    'product_name' => $products['napa-extra-500mg-tablet']['product']->name,
                    'variant_title' => $products['napa-extra-500mg-tablet']['variant']->title,
                    'sku' => $products['napa-extra-500mg-tablet']['variant']->sku,
                    'variant_options' => [],
                    'requires_prescription' => false,
                    'dosage_instructions' => '1 tablet 3 times daily after meal if fever > 100 F',
                    'quantity' => 1,
                    'unit_price' => 250.00,
                    'discount_amount' => 0.00,
                    'line_total' => 250.00,
                ]
            );

            OrderProduct::firstOrCreate(
                ['tenant_id' => $tenant->id, 'order_id' => $convertedOrder->id, 'variant_id' => $products['azithrocin-500mg-tablet']['variant']->id],
                [
                    'product_name' => $products['azithrocin-500mg-tablet']['product']->name,
                    'variant_title' => $products['azithrocin-500mg-tablet']['variant']->title,
                    'sku' => $products['azithrocin-500mg-tablet']['variant']->sku,
                    'variant_options' => [],
                    'requires_prescription' => true,
                    'dosage_instructions' => '1 tablet once daily 1 hour before meal for 5 days',
                    'quantity' => 1,
                    'unit_price' => 420.00,
                    'discount_amount' => 0.00,
                    'line_total' => 420.00,
                ]
            );
        }

        TenantPrescription::firstOrCreate(
            ['tenant_id' => $tenant->id, 'prescription_number' => 'RX-2026-005'],
            [
                'customer_id' => $customer?->id,
                'customer_name' => 'Sarah Jenkins',
                'customer_phone' => '+880 1711-223344',
                'customer_email' => 'sarah.jenkins@example.com',
                'delivery_address' => 'Apartment 4B, Green Lake Residence, Road 11, Block D, Banani',
                'delivery_city' => 'Dhaka',
                'delivery_area' => 'Banani',
                'patient_notes' => 'Please dispatch medicines as written. Urgent.',
                'prescription_image' => 'prescriptions/rx_demo_2.svg',
                'status' => 'converted_to_order',
                'pharmacist_notes' => 'Prescription verified. Contacted customer and built Order ORD-2026-RX1 with dosage instructions on packaging.',
                'order_id' => $convertedOrder->id,
                'reviewed_by_user_id' => $adminUser?->id,
                'reviewed_at' => now()->subDays(1),
                'created_at' => now()->subDays(1)->subHours(3),
            ]
        );

        // 6. Rejected Prescription (Unreadable / Expired Slip)
        TenantPrescription::firstOrCreate(
            ['tenant_id' => $tenant->id, 'prescription_number' => 'RX-2026-006'],
            [
                'customer_id' => null,
                'customer_name' => 'Kabir Hossain',
                'customer_phone' => '+880 1552-443322',
                'customer_email' => 'kabir.hossain@example.com',
                'delivery_address' => '12 Kotwali Road, Chittagong',
                'delivery_city' => 'Chittagong',
                'delivery_area' => 'Kotwali',
                'patient_notes' => 'Need emergency pain medicines.',
                'prescription_image' => 'prescriptions/rx_demo_3.svg',
                'status' => 'rejected',
                'rejection_reason' => 'The uploaded prescription is older than 6 months and doctor registration seal is cropped. Per pharmacy compliance regulations, controlled pain medications require a fresh, clear prescription slip.',
                'pharmacist_notes' => 'Phoned patient to advise consultation with a physician for an updated prescription.',
                'reviewed_by_user_id' => $adminUser?->id,
                'reviewed_at' => now()->subDays(2),
                'created_at' => now()->subDays(2)->subHours(4),
            ]
        );
    }

    private function seedCartCheckoutWithRxOrder(Tenant $tenant, ?Customer $customer, array $products, ?DeliveryCharge $deliveryCharge): void
    {
        // Journey B: Customer added items to cart and checked out, attaching their prescription photo at checkout.
        // The order sits in Pending Rx Review waiting for pharmacist verification on orders.show
        $order = Order::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_number' => 'ORD-2026-RX2'],
            [
                'public_id' => (string) Str::uuid(),
                'customer_id' => $customer?->id,
                'status' => 'pending',
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'prescription_status' => 'pending_review',
                'prescription_image' => 'prescriptions/rx_demo_3.svg',
                'is_direct_prescription' => false,
                'currency' => 'BDT',
                'subtotal' => 780.00,
                'shipping_charge' => 60.00,
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'total' => 840.00,
                'delivery_charge_id' => $deliveryCharge?->id,
                'shipping_method' => $deliveryCharge?->title ?? 'Express Delivery',
                'shipping_name' => 'Tanvir Ahmed',
                'shipping_phone' => '+880 1755-123456',
                'shipping_email' => 'tanvir.ahmed@example.com',
                'shipping_address' => 'House 12, Road 5, Sector 3, Uttara, Dhaka',
                'shipping_city' => 'Dhaka',
                'shipping_area' => 'Uttara',
                'order_note' => 'Self-service cart checkout. Uploaded Dr. Chowdhury prescription slip for post-surgery items.',
                'placed_at' => now()->subHours(3),
            ]
        );

        if (isset($products['torax-10mg-tablet']) && isset($products['montene-10mg-tablet'])) {
            OrderProduct::firstOrCreate(
                ['tenant_id' => $tenant->id, 'order_id' => $order->id, 'variant_id' => $products['torax-10mg-tablet']['variant']->id],
                [
                    'product_name' => $products['torax-10mg-tablet']['product']->name,
                    'variant_title' => $products['torax-10mg-tablet']['variant']->title,
                    'sku' => $products['torax-10mg-tablet']['variant']->sku,
                    'variant_options' => [],
                    'requires_prescription' => true,
                    'quantity' => 1,
                    'unit_price' => 300.00,
                    'discount_amount' => 0.00,
                    'line_total' => 300.00,
                ]
            );

            OrderProduct::firstOrCreate(
                ['tenant_id' => $tenant->id, 'order_id' => $order->id, 'variant_id' => $products['montene-10mg-tablet']['variant']->id],
                [
                    'product_name' => $products['montene-10mg-tablet']['product']->name,
                    'variant_title' => $products['montene-10mg-tablet']['variant']->title,
                    'sku' => $products['montene-10mg-tablet']['variant']->sku,
                    'variant_options' => [],
                    'requires_prescription' => true,
                    'quantity' => 1,
                    'unit_price' => 480.00,
                    'discount_amount' => 0.00,
                    'line_total' => 480.00,
                ]
            );
        }
    }
}
