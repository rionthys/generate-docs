<?php

namespace App\Orchid\Screens\Examples;

use App\Models\Docs;
use App\Orchid\Layouts\Examples\ChartBarExample;
use App\Orchid\Layouts\Examples\ChartLineExample;
use App\Orchid\Layouts\Examples\ChartPercentageExample;
use App\Orchid\Layouts\Examples\ChartPieExample;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Repository;

class ExampleChartsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $allDocs = Docs::all();
        $prices = $allDocs->pluck('price')->map(function ($price) {
            return (float) $price;
        });

        $pricesByDayLei = $allDocs->groupBy(function ($doc) {
            return $doc->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->pluck('price')->map(function ($price) {
                if(str_contains($price, 'Lei')){
                    return (float) $price;
                }
                else {
                    return 0;
                }
            })->sum();
        });

        $pricesByDayEuro = $allDocs->groupBy(function ($doc) {
            return $doc->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->pluck('price')->map(function ($price) {
                if(str_contains($price, '€')){
                    return (float) $price;
                }
                else {
                    return 0;
                }
            })->sum();
        });

        $pricesByDayEuro->sum();

        $totalPrice = $prices->sum();

        $countsByDay = $allDocs->groupBy(function ($doc) {
            return $doc->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        });
        return [
            'charts'  => [
                [
                    'name'   => 'Numarul',
                    'values' => $countsByDay->values()->toArray(),
                    'labels' => $countsByDay->keys()->toArray(),
                ],
                [
                    'name'   => 'Euro',
                    'values' => [$pricesByDayEuro->values()->toArray()],
                    'labels' => [$pricesByDayEuro->keys()->toArray()],
                ],
                [
                    'name'   => 'Lei',
                    'values' => [$pricesByDayLei->values()->toArray()],
                    'labels' => [$pricesByDayLei->keys()->toArray()],
                ],
            ],
            'metrics' => [
                'count'    => ['value' => number_format(Docs::count()), 'diff' => 0],
                'euro' => ['value' => number_format($pricesByDayEuro->sum()) . ' '. '€', 'diff' => 0],
                'lei'   => ['value' => number_format($pricesByDayLei->sum()) . ' '. 'Lei', 'diff' => 0],
            ],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Analitica';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return '';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @throws \Throwable
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Numarul de contracte'    => 'metrics.count',
                'Venitul contractelor in euro' => 'metrics.euro',
                'Venitul contractelor in lei' => 'metrics.lei',
            ]),

            Layout::columns([
                ChartLineExample::make('charts', 'Evoluția numărului de contracte și veniturilor')
                    ->description('Graficul arată dinamica creșterii și scăderii numărului de contracte și a venitului în funcție de timp.'),

            ]),
        ];
    }
}
