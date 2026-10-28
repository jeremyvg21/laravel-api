<?php
namespace Database\Seeders;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
class ProductCategorySeeder extends Seeder {
    public function run(): void {
        $tech = Category::firstOrCreate(['name'=>'Tecnología'],['description'=>'Accesorios y dispositivos']);
        $office = Category::firstOrCreate(['name'=>'Oficina'],['description'=>'Suministros de oficina']);
        Product::firstOrCreate(['name'=>'Mouse inalámbrico'],[
            'description'=>'Mouse óptico USB','price'=>59.90,'stock'=>18,'category_id'=>$tech->id
        ]);
        Product::firstOrCreate(['name'=>'Cuaderno A4'],[
            'description'=>'Cuaderno rayado de 100 hojas','price'=>12.50,'stock'=>42,'category_id'=>$office->id
        ]);
        Product::firstOrCreate(['name'=>'Teclado compacto'],[
            'description'=>'Teclado USB','price'=>89.00,'stock'=>9,'category_id'=>$tech->id
        ]);
    }
}
