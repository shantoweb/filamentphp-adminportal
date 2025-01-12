<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Filament\Resources\PartnerResource\RelationManagers;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Forms\Components\Grid;
use Spatie\Permission\Models\Permission;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;


class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->label(fn($livewire) => $livewire instanceof Pages\EditUser ? 'New Password' : 'Password'),

                Select::make('services')
                    ->relationship('services', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Section::make('Custom Permissions')
                    ->description('Manage Custom Permissions for a reseller which are not in the roles')
                    ->collapsible()
                    ->schema([
                        CheckboxList::make('custom_permissions')
                            ->label('')
                            ->options(function ($record) {
                                if (!$record) {
                                    return []; // Safely return an empty array if $record is null
                                }
                                
                                $assignedRolePermissions = $record->roles->flatMap(fn($role) => $role->permissions)->pluck('id')->unique() ?? collect();
                                $guardNames = $record->roles->pluck('guard_name')->unique();
                                $allPermissions = Permission::whereIn('guard_name', $guardNames)->pluck('name', 'id');
                                
                                return $allPermissions->except($assignedRolePermissions->toArray());
                            })->searchable()
                            ->afterStateHydrated(
                                fn($component, $record) => $record ? 
                                $component->state($record->permissions->pluck('id')->diff(
                                    $record->roles->flatMap(fn($role) => $role->permissions->pluck('id'))
                                )->toArray()) : $component->state([])
                            )
                            ->dehydrateStateUsing(fn($state) => collect($state)->filter()->all())
                            ->columns([
                                'sm' => 2,
                                'md' => 3,
                                'lg' => 5,
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('email')->searchable(isIndividual: true)->sortable(),
                Tables\Columns\TextColumn::make('roles')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        return $record->roles->pluck('name')->join(', ');
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->toggleable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('created_from')
                                    ->label('Created From'),
                                DatePicker::make('created_until')
                                    ->label('Created Until')
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name'),
                        TextConstraint::make('email'),
                        DateConstraint::make('created_at'),
                    ])
                    ->constraintPickerColumns(2),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->searchable();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
