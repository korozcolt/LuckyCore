<?php

namespace Database\Seeders;

use App\Enums\RaffleStatus;
use App\Enums\TicketAssignmentMethod;
use App\Models\CmsPage;
use App\Models\Raffle;
use Illuminate\Database\Seeder;

/**
 * Sample data seeder for development and testing.
 */
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRaffles();
        $this->seedCmsPages();
    }

    private function seedRaffles(): void
    {
        // Featured Active Raffle
        $raffle1 = Raffle::create([
            'title' => 'iPhone 16 Pro Max 256GB',
            'slug' => 'iphone-16-pro-max',
            'short_description' => 'Gana el nuevo iPhone 16 Pro Max con 256GB de almacenamiento.',
            'description' => '<p>Participa por el increíble <strong>iPhone 16 Pro Max</strong> con las siguientes características:</p>
                <ul>
                    <li>256GB de almacenamiento</li>
                    <li>Pantalla Super Retina XDR de 6.9"</li>
                    <li>Chip A18 Pro</li>
                    <li>Sistema de cámaras Pro</li>
                </ul>
                <p>El ganador será seleccionado utilizando los resultados de la Lotería de Bogotá.</p>',
            'ticket_price' => 5000 * 100, // $5,000 COP
            'total_tickets' => 1000,
            'sold_tickets' => 350,
            'min_purchase_qty' => 1,
            'max_purchase_qty' => 100,
            'allow_custom_quantity' => true,
            'quantity_step' => 1,
            'ticket_assignment_method' => TicketAssignmentMethod::Random,
            'status' => RaffleStatus::Active,
            'featured' => true,
            'draw_at' => now()->addDays(15),
            'lottery_source' => 'Lotería de Bogotá',
            'sort_order' => 1,
        ]);

        $raffle1->packages()->createMany([
            ['name' => 'Básico', 'quantity' => 5, 'price' => 20000 * 100, 'sort_order' => 1],
            ['name' => 'Popular', 'quantity' => 10, 'price' => 35000 * 100, 'is_recommended' => true, 'sort_order' => 2],
            ['name' => 'Premium', 'quantity' => 25, 'price' => 75000 * 100, 'sort_order' => 3],
            ['name' => 'VIP', 'quantity' => 50, 'price' => 125000 * 100, 'sort_order' => 4],
        ]);

        // Second Active Raffle
        $raffle2 = Raffle::create([
            'title' => 'PlayStation 5 + 3 Juegos',
            'slug' => 'playstation-5-bundle',
            'short_description' => 'PS5 con 3 juegos de tu elección.',
            'description' => '<p>Llévate un <strong>PlayStation 5</strong> con 3 juegos a tu elección:</p>
                <ul>
                    <li>Consola PlayStation 5 Disc Edition</li>
                    <li>Control DualSense</li>
                    <li>3 juegos de tu elección</li>
                </ul>',
            'ticket_price' => 3000 * 100,
            'total_tickets' => 500,
            'sold_tickets' => 180,
            'min_purchase_qty' => 1,
            'allow_custom_quantity' => true,
            'ticket_assignment_method' => TicketAssignmentMethod::Random,
            'status' => RaffleStatus::Active,
            'featured' => true,
            'draw_at' => now()->addDays(10),
            'lottery_source' => 'Lotería del Valle',
            'sort_order' => 2,
        ]);

        $raffle2->packages()->createMany([
            ['name' => '5 Boletos', 'quantity' => 5, 'price' => 12000 * 100, 'sort_order' => 1],
            ['name' => '10 Boletos', 'quantity' => 10, 'price' => 22000 * 100, 'is_recommended' => true, 'sort_order' => 2],
            ['name' => '20 Boletos', 'quantity' => 20, 'price' => 40000 * 100, 'sort_order' => 3],
        ]);

        // Third Active Raffle
        Raffle::create([
            'title' => 'MacBook Air M3 15"',
            'slug' => 'macbook-air-m3',
            'short_description' => 'La laptop más delgada y potente de Apple.',
            'description' => '<p>Participa por un <strong>MacBook Air M3</strong> de 15 pulgadas.</p>',
            'ticket_price' => 8000 * 100,
            'total_tickets' => 800,
            'sold_tickets' => 120,
            'min_purchase_qty' => 1,
            'ticket_assignment_method' => TicketAssignmentMethod::Random,
            'status' => RaffleStatus::Active,
            'featured' => false,
            'draw_at' => now()->addDays(20),
            'sort_order' => 3,
        ]);

        // Upcoming Raffle
        Raffle::create([
            'title' => 'Viaje a Cancún para 2 personas',
            'slug' => 'viaje-cancun-2025',
            'short_description' => 'Todo incluido por 5 noches en resort 5 estrellas.',
            'description' => '<p>Un increíble viaje a Cancún con todo incluido.</p>',
            'ticket_price' => 10000 * 100,
            'total_tickets' => 2000,
            'sold_tickets' => 0,
            'min_purchase_qty' => 1,
            'ticket_assignment_method' => TicketAssignmentMethod::Random,
            'status' => RaffleStatus::Upcoming,
            'featured' => true,
            'starts_at' => now()->addDays(5),
            'draw_at' => now()->addDays(45),
            'sort_order' => 4,
        ]);

        // Completed Raffle
        Raffle::create([
            'title' => 'Smart TV Samsung 65" QLED',
            'slug' => 'smart-tv-samsung-65',
            'short_description' => 'Sorteo finalizado - ¡Felicidades al ganador!',
            'description' => '<p>Sorteo del Smart TV Samsung 65" QLED 4K.</p>',
            'ticket_price' => 4000 * 100,
            'total_tickets' => 600,
            'sold_tickets' => 600,
            'min_purchase_qty' => 1,
            'ticket_assignment_method' => TicketAssignmentMethod::Random,
            'status' => RaffleStatus::Completed,
            'featured' => false,
            'draw_at' => now()->subDays(5),
            'sort_order' => 10,
        ]);
    }

    private function seedCmsPages(): void
    {
        CmsPage::updateOrCreate(
            ['slug' => 'como-funciona'],
            [
                'title' => 'Cómo Funciona',
                'content' => '
                    <h2>Participa en 3 simples pasos</h2>

                    <h3>1. Elige tu sorteo</h3>
                    <p>Explora nuestra selección de sorteos activos y elige el premio que más te guste. Cada sorteo tiene un número limitado de boletos disponibles.</p>

                    <h3>2. Compra tus boletos</h3>
                    <p>Selecciona la cantidad de boletos que deseas comprar. Puedes elegir entre nuestros paquetes con descuento o comprar la cantidad exacta que prefieras. El pago es 100% seguro a través de nuestras pasarelas certificadas.</p>

                    <h3>3. Recibe tus números</h3>
                    <p>Una vez confirmado tu pago, tus números de boleto serán asignados automáticamente y los podrás ver en la sección "Mis Compras". También recibirás un correo electrónico con la confirmación.</p>

                    <h2>¿Cómo se determina el ganador?</h2>
                    <p>Utilizamos los resultados oficiales de las loterías nacionales para determinar el número ganador de manera transparente y verificable. La fórmula de cálculo está publicada en cada sorteo.</p>

                    <h2>Preguntas frecuentes</h2>
                    <p>¿Tienes más dudas? Visita nuestra sección de <a href="/pagina/preguntas-frecuentes">Preguntas Frecuentes</a> o contáctanos por WhatsApp.</p>
                ',
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        CmsPage::updateOrCreate(
            ['slug' => 'terminos-y-condiciones'],
            [
                'title' => 'Términos y Condiciones',
                'content' => '
                    <h2>Términos y Condiciones de Uso</h2>

                    <p>Al utilizar nuestra plataforma y participar en nuestros sorteos, aceptas los siguientes términos y condiciones:</p>

                    <h3>1. Elegibilidad</h3>
                    <p>Debes ser mayor de 18 años para participar en nuestros sorteos. Nos reservamos el derecho de solicitar verificación de identidad.</p>

                    <h3>2. Compra de boletos</h3>
                    <p>Todas las compras son finales. Una vez procesado el pago, no se admiten devoluciones excepto en casos donde el sorteo sea cancelado.</p>

                    <h3>3. Asignación de números</h3>
                    <p>Los números de boleto se asignan de forma automática según el método configurado para cada sorteo (aleatorio o secuencial).</p>

                    <h3>4. Resultados</h3>
                    <p>Los resultados se determinan utilizando loterías oficiales y la fórmula publicada en cada sorteo. Los resultados son inapelables.</p>

                    <h3>5. Premios</h3>
                    <p>Los premios se entregan al ganador verificado. Nos reservamos el derecho de verificar la identidad del ganador antes de la entrega.</p>

                    <h3>6. Modificaciones</h3>
                    <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Los cambios serán publicados en esta página.</p>
                ',
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        CmsPage::updateOrCreate(
            ['slug' => 'preguntas-frecuentes'],
            [
                'title' => 'Preguntas Frecuentes',
                'content' => '<p>Encuentra respuestas a las preguntas más comunes sobre nuestros sorteos.</p>',
                'sections' => [
                    [
                        'question' => '¿Cómo puedo participar en un sorteo?',
                        'answer' => '<p>Es muy fácil: elige un sorteo activo, selecciona la cantidad de boletos que deseas comprar, y completa el pago. Una vez confirmado, recibirás tus números asignados.</p>',
                    ],
                    [
                        'question' => '¿Qué métodos de pago aceptan?',
                        'answer' => '<p>Aceptamos pagos a través de Wompi, MercadoPago y ePayco. Puedes pagar con tarjeta de crédito, débito, PSE, y otros métodos disponibles en cada pasarela.</p>',
                    ],
                    [
                        'question' => '¿Cómo se determina el ganador?',
                        'answer' => '<p>Utilizamos los resultados oficiales de loterías nacionales y una fórmula matemática publicada en cada sorteo para determinar el número ganador de forma transparente.</p>',
                    ],
                    [
                        'question' => '¿Cuándo recibo mis números de boleto?',
                        'answer' => '<p>Tus números son asignados inmediatamente después de que tu pago es confirmado. Los puedes ver en "Mis Compras" y también recibirás un correo electrónico.</p>',
                    ],
                    [
                        'question' => '¿Qué pasa si gano?',
                        'answer' => '<p>Te contactaremos al correo y teléfono registrados. Deberás verificar tu identidad y coordinar la entrega del premio.</p>',
                    ],
                    [
                        'question' => '¿Puedo comprar boletos sin registrarme?',
                        'answer' => '<p>Puedes agregar boletos al carrito como invitado, pero necesitarás registrarte o iniciar sesión al momento de pagar para que podamos asignarte los tickets.</p>',
                    ],
                ],
                'is_published' => true,
                'published_at' => now(),
            ]
        );
    }
}
