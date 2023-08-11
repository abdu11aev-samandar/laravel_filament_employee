<?php

namespace App\Filament\Resources\StateResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('country_id')
                    ->label('Country')
                    ->options(Country::all()->pluck('name', 'id')->toArray())
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('state_id', null)),

                Forms\Components\Select::make('state_id')
                    ->label('State')
                    ->required()
                    ->options(function (callable $get) {
                        $country = Country::find($get('country_id'));
                        if (!$country) {
                            return State::all()->pluck('name', 'id');
                        }
                        return $country->states->pluck('name', 'id');
                    })
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('city_id', null)),

                Forms\Components\Select::make('city_id')
                    ->label('City')
                    ->required()
                    ->options(function (callable $get) {
                        $state = State::find($get('state_id'));
                        if (!$state) {
                            return City::all()->pluck('name', 'id');
                        }
                        return $state->cities->pluck('name', 'id');
                    })
                    ->reactive(),

                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),
                Forms\Components\TextInput::make('first_name')->required()
                    ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')->required()
                    ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')->required()->maxLength(255),
                Forms\Components\TextInput::make('zip_code')->required()->maxLength(255),
                Forms\Components\DatePicker::make('birth_date')->required(),
                Forms\Components\DatePicker::make('date_hired')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('last_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('department.name')->sortable(),
//                Tables\Columns\TextColumn::make('city.name')->sortable(),
//                Tables\Columns\TextColumn::make('state.name')->sortable(),
                Tables\Columns\TextColumn::make('date_hired')->date(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
