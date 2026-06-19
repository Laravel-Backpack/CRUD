### Chart [PRO]

Shows a pie chart / line chart / bar chart inside a Bootstrap card, with the heading and body you specify.

To create and use a new widget chart, you need to:

**Step 1.** Install laravel-charts, that offers a single PHP syntax for 6 different charting libraries:
```
composer require consoletvs/charts:"6.*"
```

**Step 2.** Create a new ChartController:

```
php artisan backpack:chart WeeklyUsers

```

This will create:
- a new ChartController inside ```app\Http\Controllers\Admin\Charts\WeeklyUsersChartController```
- a route towards that ChartController in your ```routes/backpack/custom.php```

**Step 3.** Add the widget that points to that ChartController you just created:
```php
Widget::add([
    'type'       => 'chart',
    'controller' => \App\Http\Controllers\Admin\Charts\WeeklyUsersChartController::class,

    // OPTIONALS

    // 'class'   => 'card mb-2',
    // 'wrapper' => ['class'=> 'col-md-6'] ,
    // 'content' => [
         // 'header' => 'New Users',
         // 'body'   => 'This chart should make it obvious how many new users have signed up in the past 7 days.<br><br>',
    // ],
]);
```

**Step 4.** Configure the ChartController you just created:
- ```public function setup()``` (MANDATORY)
 - initialize and configure ```$this->chart```, using the methods detailed in the [laravel-charts documentation](https://charts.erik.cat/getting_started.html);
 - you _can_ define your dataset here, if you want your DB queries to be called upon page load;
- ```public function data()``` (OPTIONAL, but recommended)
 - use ```$this->chart->dataset()``` to configure what the chart should contain;
 - if it's defined, the chart will loads its contents using AJAX;

Optionally:
- you can _easily_ switch the JavaScript library used, by changing the use statement at the top of this file:

```diff
-use ConsoleTVs\Charts\Classes\Chartjs\Chart;
+use ConsoleTVs\Charts\Classes\Echarts\Chart;
+use ConsoleTVs\Charts\Classes\Fusioncharts\Chart;
+use ConsoleTVs\Charts\Classes\Highcharts\Chart;
+use ConsoleTVs\Charts\Classes\C3\Chart;
+use ConsoleTVs\Charts\Classes\Frappe\Chart;
```
- you can change the path to the JS library; if you don't want it loaded from a CDN, you can define ```$library``` or ```getLibraryFilePath()``` on your ChartController:

```php
protected $library = 'http://path/to/file';

// or

public function getLibraryFilePath()
{
    return asset('path/to/your/js/file');

    // or

    return [
        asset('path/to/first/js/file'),
        asset('path/to/second/js/file'),
    ];
}
```

Refresh the page to see the chart. Examples of ChartController implementations:

**Example 1:** ChartController that loads results using AJAX:
```php
<?php

namespace App\Http\Controllers\Admin\Charts;

use App\User;
use Backpack\CRUD\app\Http\Controllers\ChartController;
use Backpack\NewsCRUD\app\Models\Article;
use Backpack\NewsCRUD\app\Models\Category;
use Backpack\NewsCRUD\app\Models\Tag;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class NewEntriesChartController extends ChartController
{
    public function setup()
    {
        $this->chart = new Chart();

        // MANDATORY. Set the labels for the dataset points
        $labels = [];
        for ($days_backwards = 30; $days_backwards >= 0; $days_backwards--) {
            if ($days_backwards == 1) {
            }
            $labels[] = $days_backwards.' days ago';
        }
        $this->chart->labels($labels);

        // RECOMMENDED.
        // Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/new-entries'));

        // OPTIONAL.
        $this->chart->minimalist(false);
        $this->chart->displayLegend(true);
    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */
    public function data()
    {
        for ($days_backwards = 30; $days_backwards >= 0; $days_backwards--) {
            // Could also be an array_push if using an array rather than a collection.
            $users[] = User::whereDate('created_at', today()
                ->subDays($days_backwards))
                ->count();
            $articles[] = Article::whereDate('created_at', today()
                ->subDays($days_backwards))
                ->count();
            $categories[] = Category::whereDate('created_at', today()
                ->subDays($days_backwards))
                ->count();
            $tags[] = Tag::whereDate('created_at', today()
                ->subDays($days_backwards))
                ->count();
        }

        $this->chart->dataset('Users', 'line', $users)
            ->color('rgb(77, 189, 116)')
            ->backgroundColor('rgba(77, 189, 116, 0.4)');

        $this->chart->dataset('Articles', 'line', $articles)
            ->color('rgb(96, 92, 168)')
            ->backgroundColor('rgba(96, 92, 168, 0.4)');

        $this->chart->dataset('Categories', 'line', $categories)
            ->color('rgb(255, 193, 7)')
            ->backgroundColor('rgba(255, 193, 7, 0.4)');

        $this->chart->dataset('Tags', 'line', $tags)
            ->color('rgba(70, 127, 208, 1)')
            ->backgroundColor('rgba(70, 127, 208, 0.4)');
    }
}

```

**Example 2.** Pie chart with both labels and dataset defined in the ```setup()``` method (no AJAX):

```php
<?php

namespace App\Http\Controllers\Admin\Charts\Pies;

use Backpack\CRUD\app\Http\Controllers\ChartController;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class ChartjsPieController extends ChartController
{
    public function setup()
    {
        $this->chart = new Chart();

        $this->chart->dataset('Red', 'pie', [10, 20, 80, 30])
                    ->backgroundColor([
                        'rgb(70, 127, 208)',
                        'rgb(77, 189, 116)',
                        'rgb(96, 92, 168)',
                        'rgb(255, 193, 7)',
                    ]);

        // OPTIONAL
        $this->chart->displayAxes(false);
        $this->chart->displayLegend(true);

        // MANDATORY. Set the labels for the dataset points
        $this->chart->labels(['HTML', 'CSS', 'PHP', 'JS']);
    }
}

```
