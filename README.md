# Vehicle API

REST API σε PHP για τη διαχείριση οχημάτων, με MySQL και Docker.

## Εγκατάσταση και εκτέλεση

Το project τρέχει με Docker, Docker Compose και Bash, Curl για δοκιμές.


```bash
git clone https://github.com/OdysseasKalaitsidis/gocreations-assessment.git
cd gocreations-assessment
cp .env.example .env
docker compose up --build -d
```

Το API είναι διαθέσιμο στο:

```text
http://localhost:8088
```

### Endpoints

| Method | Endpoint | Λειτουργία |
|---|---|---|
| `GET` | `/vehicles` | Λίστα οχημάτων |
| `POST` | `/vehicles` | Δημιουργία οχήματος |
| `PUT` | `/vehicles/{id}` | Ενημέρωση οχήματος |
| `DELETE` | `/vehicles/{id}` | Διαγραφή οχήματος |

### Status codes

`200` GET και PUT 
`201` POST
`204` DELETE χωρίς body
`400` malformed JSON ή body που δεν είναι JSON object
`404` όχημα ή endpoint που δεν βρέθηκε 
`405` μη υποστηριζόμενη μέθοδος 
`422` αποτυχία validation 

Το `GET /vehicles` υποστηρίζει τα φίλτρα `price_min`, `price_max`,
`transmission`, `type_id` και sorting με `name_asc`, `name_desc`,
`price_asc`, `price_desc`.

### Τύποι οχημάτων

- `1`: car
- `2`: suv
- `3`: van

### Παράδειγμα δημιουργίας

```bash
curl -X POST http://localhost:8088/vehicles \
  -H "Content-Type: application/json" \
  --data '{
    "model_name": "Peugeot 208",
    "type_id": 1,
    "doors": 5,
    "transmission": "manual",
    "fuel": "petrol",
    "price": 110
  }'
```

Script με δοκιμή των endpoints GET, POST, PUT, DELETE και ένα άκυρο POST για άμεσο έλεγχο. Δοκιμάζονται και sorting και filtering

```bash
./test-api.sh
```

Unit tests:

```bash
docker compose exec -T api composer test
```

Τερματισμός:

```bash
docker compose down
```

## Υποθέσεις

- Οι τύποι οχημάτων αποθηκεύονται σε ξεχωριστό πίνακα.
- Το `doors` δέχεται τιμές από 1 έως 255, σύμφωνα με το `TINYINT UNSIGNED` της MySQL.
- Το `PUT` απαιτεί όλα τα πεδία του οχήματος.
- Το `price` μπορεί να είναι `0`.
- Άκυρα φίλτρα, sort και `price_min > price_max` επιστρέφουν `422`.
- Χωρίς `sort`, τα οχήματα ταξινομούνται με αύξον `id`.
- Authentication, authorization και pagination είναι εκτός scope.

## Τι θεωρώ πιο σημαντικό

Για μένα το πιο σημαντικό ήταν να μείνει ο κώδικας απλός και κατανοητός. Έβαλα το validation σε ξεχωριστή κλάση και τα queries στο repository. Ελέγχω τόσο το JSON body όσο και τα φίλτρα, ενώ για την επικοινωνία με τη βάση χρησιμοποιώ prepared statements. Επιπλέον θεώρησα σημαντικό να χρησιμοποιήσω docker για την άμεση και εύκολη δοκιμή του API.
