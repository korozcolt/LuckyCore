<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaymentGateways\Schemas;

use App\Enums\PaymentProvider;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PaymentGatewayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Gateway')
                    ->tabs([
                        // Tab 1: Información básica
                        Tabs\Tab::make('Información')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Datos del proveedor')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('provider')
                                            ->label('Proveedor')
                                            ->options(PaymentProvider::class)
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->live()
                                            ->helperText('Cada proveedor solo puede configurarse una vez'),

                                        Forms\Components\TextInput::make('display_name')
                                            ->label('Nombre a mostrar')
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('Nombre que verá el usuario en el checkout'),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Activo')
                                            ->helperText('Habilitar este método de pago')
                                            ->default(false),

                                        Forms\Components\Toggle::make('is_sandbox')
                                            ->label('Modo sandbox/pruebas')
                                            ->helperText('Usar credenciales de prueba')
                                            ->default(true),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Orden')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Orden de aparición en checkout'),

                                        Forms\Components\TextInput::make('logo_url')
                                            ->label('URL del logo')
                                            ->url()
                                            ->maxLength(500),
                                    ]),

                                Section::make('Descripción')
                                    ->schema([
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->helperText('Texto que se muestra bajo el nombre del método de pago'),
                                    ]),
                            ]),

                        // Tab 2: Credenciales
                        Tabs\Tab::make('Credenciales')
                            ->icon('heroicon-o-key')
                            ->schema([
                                // Wompi credentials
                                Section::make('Credenciales de Wompi')
                                    ->description('Obtén estas credenciales desde el panel de Wompi')
                                    ->visible(fn (Get $get) => $get('provider') === PaymentProvider::Wompi->value)
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('credentials.public_key')
                                            ->label('Llave pública')
                                            ->helperText('Empieza con pub_prod_ o pub_test_')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('credentials.private_key')
                                            ->label('Llave privada')
                                            ->password()
                                            ->revealable()
                                            ->helperText('Empieza con prv_prod_ o prv_test_')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('credentials.events_secret')
                                            ->label('Secreto de eventos (webhook)')
                                            ->password()
                                            ->revealable()
                                            ->helperText('Para verificar la firma de los webhooks')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('credentials.integrity_secret')
                                            ->label('Secreto de integridad')
                                            ->password()
                                            ->revealable()
                                            ->helperText('Para generar la firma del widget')
                                            ->maxLength(255),
                                    ]),

                                // MercadoPago credentials
                                Section::make('Credenciales de MercadoPago')
                                    ->description('Obtén estas credenciales desde el panel de MercadoPago Developers')
                                    ->visible(fn (Get $get) => $get('provider') === PaymentProvider::MercadoPago->value)
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('credentials.public_key')
                                            ->label('Public Key')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('credentials.access_token')
                                            ->label('Access Token')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255),
                                    ]),

                                // ePayco credentials
                                Section::make('Credenciales de ePayco')
                                    ->description('Obtén estas credenciales desde el panel de ePayco')
                                    ->visible(fn (Get $get) => $get('provider') === PaymentProvider::Epayco->value)
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('credentials.public_key')
                                            ->label('Public Key')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('credentials.private_key')
                                            ->label('Private Key')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('credentials.p_cust_id_cliente')
                                            ->label('P_CUST_ID_CLIENTE')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('credentials.p_key')
                                            ->label('P_KEY')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(255),
                                    ]),
                            ]),

                        // Tab 3: Información adicional
                        Tabs\Tab::make('Información de integración')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Section::make('URLs de webhook')
                                    ->description('Configura estas URLs en el panel de tu proveedor de pagos')
                                    ->schema([
                                        Forms\Components\Placeholder::make('webhook_url')
                                            ->label('URL del webhook')
                                            ->content(function (Get $get): string {
                                                $provider = $get('provider');
                                                if (! $provider) {
                                                    return 'Selecciona un proveedor primero';
                                                }
                                                $providerValue = $provider instanceof PaymentProvider
                                                    ? $provider->value
                                                    : $provider;

                                                return url("/api/webhooks/payments/{$providerValue}");
                                            }),

                                        Forms\Components\Placeholder::make('callback_info')
                                            ->label('URL de redirección')
                                            ->content('La URL de redirección se genera dinámicamente para cada transacción'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
