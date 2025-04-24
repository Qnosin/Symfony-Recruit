# 📦 Symfony Stock API
<p> Aplikacja oparta o Framework Symfony do wyświetlania stanu magazynowego oraz importowania danych z plików CSV pochodzących od różnych dostawców. </p>


## 🚀 Features

- REST API w formacie JSON (GET)
- Obsługa plików CSV o różnych strukturach
- Dedykowane procesory transformujące dane
- Funkcjonalne testy API (PHPUnit + WebTestCase)
- Obsługa Dockera


## 🐳 Uruchomienie z Docker

```bash
git clone https://github.com/Qnosin/Symfony-Recruit.git
cd Symfont-Recruit
docker-compose up -d
docker exec -it symfony_app composer install
docker exec -it symfony_app php bin/console doctrine:database:create // if not exist 
docker exec -it symfony_app php bin/console doctrine:schema:update --force
docker exec -it symfony_app php bin/console doctrine:database:create --env=test
docker exec -it symfony_app php bin/console doctrine:schema:update --force --env=test
```



## 🧾Import CSV
Projekt zawiera konsolową komendę  do importowania danych z plików CSV.
```bash
docker exec -it symfony_app php bin/console app:import-csv /absolute/path/to/file.csv SUPPLIER_NAME
```
Gdzie absolute-path to: /app/data/nazwa_pliku.csv
<p>SUPPLIER_NAME to: lorotom, trah </p> 
<p>wszystkie dostepnę pliki znajdują się w katalogu /data/</p>




## 📡 Endpoint API

**GET** `/api/get-stocks`

### Parametry (query):

- `mpn` *(opcjonalny, wymagany gdy brak `ean`)*
- `ean` *(opcjonalny, wymagany gdy brak `mpn`)*

### Przykłady:

```http
GET /api/get-stocks?mpn=19-598
GET /api/get-stocks?ean=7612720201662
GET /api/get-stocks?mpn=19-598&ean=7612720201662
```

```bash
[
  {
    "ean": "7612720201662",
    "mpn": "19-598",
    "producer": "ExampleProducer",
    "externalId": "000 013",
    "price": 100,
    "quantity": 10
  }
]
```



##  🗃️ Struktura danych w Stock Items

| Pole        | Typ           | Uwagi                             |
|-------------|---------------|-----------------------------------|
| `ean`       | `string|null` | Może być nullem                   |
| `mpn`       | `string`      | Manufacturer Part Number (MPN)   |
| `producer`  | `string`      | Producent                         |
| `externalId`| `string`      | Zewnętrzne ID                     |
| `price`     | `float`       | Cena jednostkowa                  |
| `quantity`  | `int`         | dostępna Ilość                    |




## 🧪 Testy

W projekcie znajdują się testy funcjonalne jak i unit testy, były przezemnie konfigurowane za pomocą środowiska phpstorm

### Konfiguracja środowiska testowego w PhpStorm:

1. Otwórz **Settings / **PHP**.
2. W sekcji **CLI Interpreter** dodaj nowy interpreter typu **Docker compose**.
3. wybieramy server : docker
4. Wybieramy kontener o nazwie app
5. Ustaw go jako domyślny dla projektu.

### Uruchamianie testów:

- W terminalu wpisujemy
  ```bash
   docker exec -it symfony_app php bin/phpunit tests/functional/StockApiControllerTest.php // by sprawdzić testy funkcjonalne
   docker exec -it symfony_app php bin/phpunit tests/Unit/CreateDataFromCsvCommandTest.php // by sprawdzic testy jednostkowe
  ```

## Pokrycie testami

Testowana jest funkcjonalność endpointu `GET /api/get-stocks`:

- ✅ Zwracanie danych na podstawie `mpn`
- ✅ Zwracanie danych na podstawie `mpn` i `ean`
- ✅ Walidacja brakujących parametrów (`400 BAD REQUEST`)

Testy funkcjonalne korzystają z bazy danych `app_test`, która może być tworzona i aktualizowana automatycznie w razie potrzeby.


##  Next Steps

1. **Dodanie Frontendu dla Użytkownika**
   - Stworzenie prostego frontendu, który umożliwi użytkownikom interakcję z API bez potrzeby używania Postmana. Frontend pozwoli na łatwe wyszukiwanie pojedynczych danych.
2. **Refaktoryzacja Testów**
   - Refaktoryzacja testów, aby dla każdego dostawcy stworzyć osobne testy jednostkowe (unit tests).
   - Utworzenie osobnych testów funkcjonalnych dla każdego dostawcy, aby upewnić się, że system działa zgodnie z wymaganiami dla różnych formatów CSV.
3. **Dodanie Autoryzacji i Uwierzytelniania za pomocą JWT**
   - Wdrożenie JWT (JSON Web Token) do autoryzacji i uwierzytelniania użytkowników API.
4. **Walidacja Danych Wchodzących w Procesory CSV**
   - Dodanie walidacji danych wejściowych w transformatorach CSV dla każdego dostawcy.




