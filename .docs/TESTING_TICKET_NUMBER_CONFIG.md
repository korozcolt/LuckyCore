# Testing - Configuración de Números de Tickets

## Tests Automatizados

### Tests Unitarios/Feature
Los tests están en `tests/Feature/RaffleTicketNumberConfigTest.php` y cubren:

1. ✅ Valores por defecto de configuración
2. ✅ Creación con configuración personalizada
3. ✅ Validaciones de rango (max >= min)
4. ✅ Validaciones de rango suficiente para total_tickets
5. ✅ Validaciones de dígitos (3-10)
6. ✅ Formato dinámico de códigos de tickets

Ejecutar tests:
```bash
php artisan test --filter RaffleTicketNumberConfigTest
```

## Testing Manual con Chrome DevTools

### 1. Acceder al Panel Admin

1. Abrir navegador en `https://luckycore.test/admin`
2. Iniciar sesión con usuario admin
3. Navegar a **Sorteos** → **Crear nuevo sorteo**

### 2. Verificar Campos en el Formulario

**Ubicación**: Tab "Sorteo" → Sección "Configuración de números de tickets"

**Campos a verificar**:
- ✅ **Cantidad de dígitos**: Campo numérico (3-10)
- ✅ **Número mínimo**: Campo numérico
- ✅ **Número máximo**: Campo numérico (auto-calculado)

**Con Chrome DevTools**:
1. Abrir DevTools (F12)
2. Ir a la pestaña **Elements**
3. Buscar los campos por sus labels:
   ```html
   <input name="ticket_digits" />
   <input name="ticket_min_number" />
   <input name="ticket_max_number" />
   ```

### 3. Probar Auto-cálculo de Número Máximo

**Pasos**:
1. En el campo "Cantidad de dígitos", cambiar de 5 a 6
2. **Verificar en DevTools**:
   - Abrir pestaña **Console**
   - El campo `ticket_max_number` debe actualizarse automáticamente a `999999`
3. Cambiar a 7 dígitos → debe actualizarse a `9999999`

**Verificación en DevTools**:
```javascript
// En la consola de Chrome DevTools
// Verificar que el valor se actualiza
document.querySelector('input[name="ticket_max_number"]').value
// Debe mostrar el valor calculado
```

### 4. Probar Validaciones

**Test 1: Rango insuficiente**
1. Configurar:
   - Total tickets: 10000
   - Dígitos: 5
   - Min: 1
   - Max: 5000 (insuficiente)
2. Intentar guardar
3. **Verificar en DevTools**:
   - Abrir pestaña **Network**
   - Verificar que la petición POST falla
   - Ver mensaje de error en la respuesta

**Test 2: Max < Min**
1. Configurar:
   - Min: 1000
   - Max: 500 (inválido)
2. Intentar guardar
3. Debe mostrar error de validación

**Test 3: Dígitos fuera de rango**
1. Intentar poner 2 dígitos → debe rechazar
2. Intentar poner 11 dígitos → debe rechazar

### 5. Verificar Persistencia

1. Crear sorteo con configuración personalizada:
   - Dígitos: 6
   - Min: 100000
   - Max: 199999
2. Guardar y verificar en base de datos:
   ```sql
   SELECT ticket_digits, ticket_min_number, ticket_max_number 
   FROM raffles 
   WHERE slug = 'tu-sorteo';
   ```
3. Editar el sorteo y verificar que los valores se cargan correctamente

### 6. Verificar Formato de Tickets

**Cuando se generen tickets** (en futura implementación):
1. Crear sorteo con 6 dígitos, min=100000, max=199999
2. Generar tickets
3. **Verificar en DevTools**:
   - Abrir pestaña **Application** → **Local Storage** o **Session Storage**
   - O verificar en la base de datos que los códigos están en el rango correcto
   - Verificar que el formato usa 6 dígitos con padding

### 7. Verificar Retrocompatibilidad

1. Verificar sorteos existentes (creados antes de la migración):
   - Deben tener valores por defecto: 5 dígitos, min=1, max=99999
2. Editar un sorteo existente y verificar que los campos se muestran con valores por defecto

## Checklist de Testing Manual

- [ ] Campos visibles en formulario de creación
- [ ] Auto-cálculo de max_number funciona al cambiar dígitos
- [ ] Validación de rango insuficiente funciona
- [ ] Validación de max < min funciona
- [ ] Validación de dígitos fuera de rango funciona
- [ ] Valores se guardan correctamente en BD
- [ ] Valores se cargan correctamente al editar
- [ ] Sorteos existentes tienen valores por defecto
- [ ] Formato de tickets usa la configuración del sorteo (cuando se implemente generación)

## Comandos Útiles para Testing

```bash
# Ver datos en BD
php artisan tinker
>>> \App\Models\Raffle::latest()->first()->only(['ticket_digits', 'ticket_min_number', 'ticket_max_number']);

# Ejecutar tests
php artisan test --filter RaffleTicketNumberConfigTest

# Ver migraciones
php artisan migrate:status
```
