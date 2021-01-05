<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Category;
use App\Models\Company;
use App\Models\SubCategory;
use App\Models\Website;
use function GuzzleHttp\json_decode;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class PopulateDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:db
            {path : Path to the json file}
            {--fresh : Fresh the data base before populate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = trim($this->input->getArgument('path'));
        $URL_PATTERN = "/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9.\+]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/";
        $EMAIL_PATTERN = "/((?:[\w\d]+[.%-]?)+@(?:[[:alnum:]-]+\.)+[a-z]{2,})/";
        if ($this->option('fresh')) {
            $this->call('migrate:fresh');
        }

        $jsonData = json_decode($this->files->get($path));
        foreach ($jsonData as $catData) {
            $this->info($catData[0]->name);
            $catAttrs = (array) $catData[0];
            $catAttrs['slug'] = Str::slug($catData[0]->name);
            $category = new Category($catAttrs);
            $category->save();

            $subCatRel = $category->subCategories();
            foreach ($catData[1] as $subCatData) {
                $subCatAttrs = [];
                $subCatAttrs['name'] = trim(Str::before($subCatData->name, '- MALI'));
                $subCatAttrs['slug'] = Str::slug($subCatData->name);
                $subCategory = new SubCategory($subCatAttrs);
                $subCatRel->save($subCategory);

                $compnyRel = $subCategory->companies();

                foreach ($subCatData->items as $cmpnyData) {
                    $cmpnyAttrs = [];
                    $cmpnyAttrs['name'] = $cmpnyData->name;
                    $cmpnyAttrs['slug'] = Str::slug($cmpnyData->name);

                    $company = Company::firstOrNew($cmpnyAttrs);
                    if (! $company->exists) {
                        $company->save();

                        $compnyRel->syncWithoutDetaching([$company->id]);
                    }
                    $addr = [];

                    $addr['city'] = Str::after($cmpnyData->address, '- ');
                    $addr['formatted_address'] = $cmpnyData->address;
                    $addr['country'] = Str::contains(Str::lower($cmpnyData->address), 'paris') ? 'France' : 'Mali';

                    // $company = new Company();

                    $address = new Address($addr);

                    $company->address()->save($address);

                    $phones = [];
                    $emailr = null;
                    $email = null;
                    $websites = [];
                    foreach (explode('\n', $cmpnyData->description[0]) as $info) {
                        $info = trim($info);
                        $links = [];
                        if (preg_match($EMAIL_PATTERN, $info, $emailr) == 1) {
                            $email = $emailr[0];
                        } elseif (preg_match($URL_PATTERN, $info, $links) == 1) {
                            $websites[] = new Website([
                                'type' => Str::contains($links[0], 'facebook') ? 'Facebook' : 'Website',
                                'url'  => $links[0],
                            ]);
                        } else {
                            if ($info) {
                            }
                        }

                        if ($email) {
                            $company->email = $email;
                            $company->save();
                        }

                        $company->websites()->saveMany($websites);
                        $company->phones()->saveMany($phones);
                    }
                }
            }
        }

        $this->line("<info>Path to the Json file :</info> {$path}");
        //
    }
}
