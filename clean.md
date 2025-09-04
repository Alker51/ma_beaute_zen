# Refactorisation & Mutualisation du Code – Ma Beauté Zen

## Objectif

Optimiser la maintenabilité et centraliser les logiques récurrentes du projet en limitant la redondance, notamment concernant :
- Les états (constantes, couleurs, libellés).
- Les conversions de durée.
- Les libellés fixes (“en minutes”).

---

## 1️⃣ États de réservation (Constantes PENDING_STATE, etc.)

**Constat**  
Les constantes d’état sont dupliquées et utilisées directement via `StateController::CONSTANTE` dans plusieurs fichiers.

**Solution recommandée** :  
Utiliser une `enum` PHP centrale pour gérer tous les états.

**Exemple à créer** `src/Enum/BookingState.php` :

```php
<?php
namespace App\Enum;

enum BookingState: int
{
    case PENDING = 1;
    case FINISH = 2;
    case DISCONTINUED = 3;
    case VALIDATED = 4;
    case PENDING_VALIDATION = 5;
    case REFUSED = 6;
    case EXECUTED = 7;
    case CANCELLED = 8;
}
```
**Utilisation partout dans le projet** :

```php
use App\Enum\BookingState;
BookingState::PENDING->value;
if ($booking->getState() === BookingState::VALIDATED->value) { ... }
```
---

## 2️⃣ Mapping des états vers couleurs/badges

**Constat**  
Des méthodes similaires (switch/case, “mapping couleur”) sont dupliquées dans plusieurs fichiers.

**Solution recommandée** :  
Ajouter les méthodes directement dans l’`enum` BookingState.

**Exemple** (dans `BookingState.php`) :

```php
public function bootstrapColor(): string
{
    return match($this) {
        BookingState::PENDING, BookingState::PENDING_VALIDATION => 'info',
        BookingState::VALIDATED, BookingState::EXECUTED, BookingState::FINISH => 'success',
        BookingState::REFUSED, BookingState::CANCELLED, BookingState::DISCONTINUED => 'danger',
        default => 'primary',
    };
}

public function color(): string
{
    return match($this) {
        BookingState::PENDING, BookingState::PENDING_VALIDATION => '#FFCC00',
        BookingState::VALIDATED, BookingState::EXECUTED, BookingState::FINISH => '#006600',
        BookingState::REFUSED, BookingState::CANCELLED, BookingState::DISCONTINUED => '#CC0000',
        default => '#6699FF'
    };
}

public function textColor(): string
{
    return match($this) {
        BookingState::VALIDATED, BookingState::EXECUTED, BookingState::DISCONTINUED, BookingState::CANCELLED, BookingState::REFUSED, BookingState::FINISH => '#FFFFFF',
        default => '#000000'
    };
}
```
**Utilisation** :

```php
$etat = BookingState::tryFrom($booking->getState());
$etat?->bootstrapColor();
$etat?->color();
$etat?->textColor();
```
---

## 3️⃣ Libellé “(en minutes)”

**Constat**  
Le texte “(en minutes)” est présent à plusieurs endroits.

**Solution recommandée** :
- **Option 1 :** Utiliser le système de traduction Symfony dans `translations/messages.fr.yaml` :
    ```yaml
    duration_label_minutes: "(en minutes)"
    ```
    Et utiliser dans les vues/
    ```twig
    {{ 'duration_label_minutes'|trans }}
    ```
- **Option 2 :** Créer une constante dans une classe “Labels” :
    ```php
    class Labels {
        public const DELAY_MINUTES = ' (en minutes)';
    }
    ```
    Utilisation : `Labels::DELAY_MINUTES`

---

## 4️⃣ Conversion minutes → heures/minutes

**Constat**  
La logique “minutes → heure(s) + minute(s)” est répétée.

**Solution recommandée** :  
Créer un helper ou service unique pour ce calcul.

**Exemple, dans `App\Utils\TimeFormatter.php`** :
```php
namespace App\Utils;

class TimeFormatter
{
    public static function minutesToHourMinute(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;
        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' heure' . ($hours > 1 ? 's' : '');
        }
        if ($remaining > 0) {
            $parts[] = $remaining . ' minute' . ($remaining > 1 ? 's' : '');
        }
        return implode(' ', $parts);
    }
}
```
**Utilisation :**
```php
TimeFormatter::minutesToHourMinute($delay);
```
Ou en créant un **Twig filter** pour l’utiliser dans les templates.

---

## 5️⃣ Éviter les instanciations directes de contrôleurs

**Constat**  
Certains fichiers font `new StateController()` (anti-pattern Symfony).

**Solution recommandée** :
- **Utiliser l'Enum ou un service** (voir plus haut).
- **Jamais d’instanciation manuelle de contrôleur :** privilégier les services ou l’Enum centralisée.

---

## **Récapitulatif des bonnes pratiques**

- **Enum centralisée pour les états et leurs propriétés.**
- **Utilisation du système de traduction ou classe constante pour les labels fixes.**
- **Un helper/filtre unique pour la conversion minutes → heure(s)/minute(s).**
- **Suppression des instanciations manuelles de contrôleur (favoriser Enum ou service).**

---

## Exemple d’organisation finale
```bash

src/
├── Enum/
│   └── BookingState.php
├── Utils/
│   └── TimeFormatter.php
├── Controller/
├── ...
translations/
    └── messages.fr.yaml
```
---

**En suivant ces recommandations, tu facilites la maintenance, la clarté et l’évolution de ton projet !**

