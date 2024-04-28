<?php


namespace App\Orchid\Screens\Examples;

use App\Models\Docs;
use App\Orchid\Layouts\Examples\ChartBarExample;
use App\Orchid\Layouts\Examples\ChartLineExample;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Components\Cells\DateTimeSplit as DateTimeSplitCell;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Menu;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\DateRange;

class ExampleScreen extends Screen
{
    /**
     * Fish text for the table.
     */
    public const TEXT_EXAMPLE = 'Lorem ipsum at sed ad fusce faucibus primis, potenti inceptos ad taciti nisi tristique
    urna etiam, primis ut lacus habitasse malesuada ut. Lectus aptent malesuada mattis ut etiam fusce nec sed viverra,
    semper mattis viverra malesuada quam metus vulputate torquent magna, lobortis nec nostra nibh sollicitudin
    erat in luctus.';

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'table' => Docs::filters()->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Lista de contracte';
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
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('table', [

                TD::make('name', 'Numele contractului')
                    ->filter(Input::make())
                    ->width(550)
                    ->sort(),


                TD::make('price', 'Pretul')
                    ->width('100')
                    ->filter(Input::make())
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('created_at', 'Data crearii')
                    ->width('200')
                    ->filter(DateRange::make('rent_start_date')
                        ->placeholder('')
                        ->format('d.m.Y H:i')
                        ->allowInput()
                        ->enableTime())
                    ->usingComponent(DateTimeSplitCell::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('Vizualizare')
                    ->alignRight()
                    ->render(function (Docs $doc) {
                        return
                            Link::make('')
                                ->icon('folder')
                                ->href('https://docs.google.com/gview?url=https://egcho-cloud.store/docs/'.urlencode($doc->name).'&embedded=false');
                    })->width('20')
                    ->align(TD::ALIGN_CENTER),

                TD::make('Stergere')
                    ->alignRight()
                    ->render(function (Docs $doc) {
                        return Button::make('')
                                ->icon('trash')
                                ->method('deleteDoc', [
                                    'id' => $doc->id,
                                ]);
                    })->width('1')
                    ->align(TD::ALIGN_CENTER),
            ]),
        ];
    }

    public function showToast(Request $request): void
    {
        Toast::warning($request->get('toast', "Documentul a fost generat cu succes!"));
    }

    public function deleteDoc()
    {
        $doc = Docs::where('id', $_GET['id']);

        $doc->delete();
    }
}
