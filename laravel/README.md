

## Projekto aprašymas

Chirper – Laravel karkasu sukurta mikroblogų interneto programa.
Vartotojai gali registruotis, prisijungti, kurti trumpus įrašus,
juos peržiūrėti, redaguoti ir ištrinti.

Projektas sukurtas atliekant oficialų Laravel kursą:
https://laravel.com/learn/getting-started-with-laravel

## Naudotos technologijos

- PHP
- Laravel
- Blade
- SQLite arba kita projekte sukonfigūruota duomenų bazė
- JavaScript ir CSS
- Git ir GitHub

## Individualūs pakeitimai

- Pakeistas programos pavadinimas.
- Poraštėje naudojant Blade rodomas mokinio vardas,
  pavardė ir klasė.
- Pakeistos vartotojo sąsajos spalvos.

## Reikalavimai

- PHP versija, atitinkanti composer.json
- Composer
- Node.js ir npm
- Projekto palaikoma duomenų bazė

## Paleidimas

1. Nukopijuokite saugyklą į kompiuterį.
2. Atidarykite terminalą kataloge laravel.
3. Įdiekite PHP priklausomybes:

   composer install

4. Sukurkite vietinį aplinkos failą:

   cp .env.example .env

   Windows aplinkoje failą galima nukopijuoti rankiniu būdu.

5. Sukurkite programos raktą:

   php artisan key:generate

6. Sukonfigūruokite duomenų bazę faile .env.
7. Paleiskite migracijas:

   php artisan migrate

8. Įdiekite JavaScript priklausomybes ir sukurkite išteklius:

   npm install
   npm run build

9. Paleiskite vietinį serverį:

   php artisan serve

10. Atidarykite http://127.0.0.1:8000