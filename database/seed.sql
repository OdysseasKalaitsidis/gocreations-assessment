START TRANSACTION;

INSERT INTO vehicle_types (name)
VALUES
    ('car'),
    ('suv'),
    ('van');

INSERT INTO vehicles (
    model_name,
    type_id,
    doors,
    transmission,
    fuel,
    price
)
VALUES
    (
        'Fiat Panda',
        (SELECT id FROM vehicle_types WHERE name = 'car'),
        5,
        'manual',
        'petrol',
        90.00
    ),
    (
        'Volkswagen Golf',
        (SELECT id FROM vehicle_types WHERE name = 'car'),
        5,
        'automatic',
        'diesel',
        145.00
    ),
    (
        'Nissan Leaf',
        (SELECT id FROM vehicle_types WHERE name = 'car'),
        5,
        'automatic',
        'electric',
        160.00
    ),
    (
        'Toyota RAV4',
        (SELECT id FROM vehicle_types WHERE name = 'suv'),
        5,
        'automatic',
        'hybrid',
        190.00
    ),
    (
        'Ford Transit',
        (SELECT id FROM vehicle_types WHERE name = 'van'),
        4,
        'manual',
        'diesel',
        170.00
    );

COMMIT;
