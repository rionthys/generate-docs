<?php

namespace App\Orchid\Screens\Examples;

use App\Models\Docs;
use App\Orchid\Layouts\Examples\ExampleElements;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\DateTimeSplit;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Fields\Radio;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;
use \PhpOffice\PhpWord\PhpWord;
use NumberToWords\NumberToWords;

class ExampleFieldsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public const SOURCE_DOC = '/upload/source.docx';
    public const DIST_DIRECTORY = '/upload/';

    public function query(): iterable
    {
        return [];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Generarea de contract';
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
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            ExampleElements::class,
            Layout::columns([
                Layout::rows([
                    Input::make('user_name')
                        ->type('text')
                        ->title('Nume/Prenume/Patronimic')
                        ->horizontal(),

                    Input::make('user_birth')
                        ->type('text')
                        ->title('Data/luna/anul nașterii')
                        ->horizontal(),

                    Input::make('user_code')
                        ->type('text')
                        ->title('Cod personal')
                        ->horizontal(),

                    Input::make('user_address')
                        ->type('text')
                        ->title('Domiciliu')
                        ->horizontal(),

                    Input::make('user_phone')
                        ->type('text')
                        ->title('Numarul de telefon')
                        ->horizontal(),

                    Input::make('car_brand')
                        ->type('text')
                        ->title('Marca')
                        ->horizontal(),

                    Input::make('car_model')
                        ->type('text')
                        ->title('Model')
                        ->horizontal(),

                    Input::make('car_number')
                        ->type('text')
                        ->title('Numarul de înmatriculare')
                        ->horizontal(),

                    Input::make('car_color')
                        ->type('text')
                        ->title('Culoarea')
                        ->horizontal(),

                    Input::make('car_mileage')
                        ->type('text')
                        ->title('Rulajul curent')
                        ->horizontal(),

                    Input::make('car_fuel_volume')
                        ->type('text')
                        ->title('Volumul combustibilului curent')
                        ->horizontal(),

                    /** todo change rent_days to rent_final_date - rent_start_date */
                    DateTimer::make('rent_start_date')
                        ->title('Data preluării')
                        ->placeholder('')
                        ->format('d.m.Y H:i')
                        ->allowInput()
                        ->enableTime()
                        ->horizontal(),

                    DateTimer::make('rent_final_date')
                        ->title('Data returnării')
                        ->placeholder('')
                        ->format('d.m.Y H:i')
                        ->allowInput()
                        ->enableTime()
                        ->horizontal(),

                    Input::make('rent_price_day')
                        ->type('text')
                        ->title('Prețul pe zi')
                        ->horizontal(),

                    Input::make('rent_price_total')
                        ->type('text')
                        ->title('Prețul Total')
                        ->horizontal(),

                    Input::make('rent_guarantee_price')
                        ->type('text')
                        ->title('Suma garanţiei')
                        ->horizontal(),

                    Select::make('rent_guarantee_currency')->empty('€', '€')
                        ->options([
                            'Lei' => 'Lei',
                        ])
                        ->allowAdd()
                        ->title('Valuta garanţiei')
                        ->horizontal(),

                    Input::make('mileage_limit_number')
                        ->type('text')
                        ->title('Limita de km')
                        ->horizontal(),

                    Button::make('Genereaza')
                        ->method('generateDoc')
                        ->type(Color::BASIC),
                ]),
            ]),
        ];
    }

    private function getDiffTime($start, $end)
    {
        setlocale(LC_TIME, 'ro_MD.UTF-8');
        if (strtotime($end) > strtotime($start)) {
            $now = strtotime($start);
            $your_date = strtotime($end);
            $datediff = $now - $your_date;

            return abs(round($datediff / (60 * 60 * 24)));
        } else {
            $now = strtotime($end);
            $your_date = strtotime($start);
            $datediff = $now - $your_date;

            return abs(round($datediff / (60 * 60 * 24)));
        }

    }

    private function isValidField($post, $field)
    {
        return isset($post[$field]) && !empty($post[$field]);
    }

    private function numberToWord($number)
    {
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('ro');
        return $numberTransformer->toWords($number);
    }

    private function getDocFields($post)
    {
        setlocale(LC_TIME, 'ro_MD.UTF-8');
        $empty_space = '_________________';
        $fields_user = [
            '{user_name}' => $this->isValidField($post, 'user_name') ? $post['user_name'] : $empty_space,
            '{user_email}' => $this->isValidField($post, 'user_email') ? $post['user_email'] : $empty_space,
            '{user_birth}' => $this->isValidField($post, 'user_birth') ? $post['user_birth'] : $empty_space,
            '{user_code}' => $this->isValidField($post, 'user_code') ? $post['user_code'] : $empty_space,
            '{user_address}' => $this->isValidField($post, 'user_address') ? $post['user_address'] : $empty_space,
            '{user_phone}' => $this->isValidField($post, 'user_phone') ? $post['user_phone'] : $empty_space,
        ];
        $fields_car = [
            '{car_brand}' => $this->isValidField($post, 'car_brand') ? $post['car_brand'] : $empty_space,
            '{car_model}' => $this->isValidField($post, 'car_model') ? $post['car_model'] : $empty_space,
            '{car_number}' => $this->isValidField($post, 'car_number') ? $post['car_number'] : $empty_space,
            '{car_color}' => $this->isValidField($post, 'car_color') ? $post['car_color'] : $empty_space,
            '{car_mileage}' => $this->isValidField($post, 'car_mileage') ? $post['car_mileage'] : $empty_space,
            '{car_fuel_volume}' => $this->isValidField($post, 'car_fuel_volume') ? $post['car_fuel_volume'] : $empty_space,
        ];

        $fields_rent = [
            /** todo change rent_days to rent_final_date - rent_start_date */
            '{rent_days_number}' => $this->isValidField($post, 'rent_start_date') && $this->isValidField($post, 'rent_final_date') ? $this->getDiffTime($post['rent_start_date'], $post['rent_final_date']) : $empty_space,
            '{rent_days_word}' => $this->isValidField($post, 'rent_start_date') && $this->isValidField($post, 'rent_final_date') ? $this->numberToWord($this->getDiffTime($post['rent_start_date'], $post['rent_final_date'])) : $empty_space,
            '{rent_price_day}' => $this->isValidField($post, 'rent_price_day') ? $post['rent_price_day'] : $empty_space,
            /** todo add selection for auto calculate */
            '{rent_price_total}' => $this->isValidField($post, 'rent_price_total') ? $post['rent_price_total'] : $empty_space,
            '{rent_guarantee_price}' => $this->isValidField($post, 'rent_guarantee_price') ? $post['rent_guarantee_price'] : $empty_space,
            '{rent_guarantee_currency}' => $this->isValidField($post, 'rent_guarantee_currency') ? $post['rent_guarantee_currency'] : $empty_space,
            '{rent_start_date}' => $this->isValidField($post, 'rent_start_date') ? explode(' ', $post['rent_start_date'])[0] : $empty_space,
            '{rent_start_hour}' => $this->isValidField($post, 'rent_start_date') ? explode(' ', $post['rent_start_date'])[1] : $empty_space,
            '{rent_final_date}' => $this->isValidField($post, 'rent_final_date') ? explode(' ', $post['rent_final_date'])[0] : $empty_space,
            '{rent_final_hour}' => $this->isValidField($post, 'rent_final_date') ? explode(' ', $post['rent_final_date'])[1] : $empty_space,
            '{mileage_limit_number}' => $this->isValidField($post, 'mileage_limit_number') ? $post['mileage_limit_number'] : $empty_space,
            '{mileage_words_limit}' => $this->isValidField($post, 'mileage_limit_number') ? $this->numberToWord($post['mileage_limit_number']) : $empty_space,
        ];

        $fields = [
            /** todo add checkbox to auto complete this */
            '{current_date}' => date('d.m.Y', time()),
        ];

        foreach ($fields_user as $key => $field) {
            $fields[$key] = $field;
        }

        foreach ($fields_car as $key => $field) {
            $fields[$key] = $field;
        }

        foreach ($fields_rent as $key => $field) {
            $fields[$key] = $field;
        }

        return $fields;
    }

    public function generateDoc()
    {
        try {
            setlocale(LC_TIME, 'ro_MD.UTF-8');
            $personal_code = isset($_POST['personal_code']) && !empty($_POST['personal_code']) ? $_POST['personal_code'] . '_' : '';
            $user_name = isset($_POST['user_name']) && !empty($_POST['user_name']) ? $_POST['user_name'] . '_' : '';
            $targetFileName = 'contractul_' . $personal_code . $user_name . date('d.m.Y H:i', time()) . '.docx';

            $phpWord = new PhpWord();
            $document = $phpWord->loadTemplate(base_path() . self::SOURCE_DOC);

            $variables = $this->getDocFields($_POST);
            $arVar = [];
            foreach ($variables as $search => $replace) {
                $arVar[] = [$search, $replace];
                $document->setValue($search, $replace);
            }

            $document->saveAs(base_path() . self::DIST_DIRECTORY . $targetFileName);

            Docs::create(['name' => $targetFileName, 'price' => $_POST['rent_price_total'] . ' ' . $_POST['rent_guarantee_currency']]);
            Alert::success('Documentul a fost generat cu succes!');
        } catch (\Error $e) {
            Alert::error('Eroare in generarea documentului');
        }
    }
}
