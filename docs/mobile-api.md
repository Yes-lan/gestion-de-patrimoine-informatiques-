# API mobile Xynpo

## Authentification

### `POST /api/mobile/login`
Body JSON:

```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

Réponse:

```json
{
  "token": "...",
  "token_type": "Bearer",
  "expires_in": 86400,
  "user": { "id": 1, "email": "...", "roles": ["ROLE_ADMIN"] }
}
```

Ensuite envoyer `Authorization: Bearer <token>`.

## Endpoints

- `GET /api/mobile/me`
- `GET /api/mobile/patients`
- `GET /api/mobile/patients/{id}`
- `GET /api/mobile/rdv?patientId=123`
- `GET /api/mobile/patients/{id}/photos`
- `POST /api/mobile/patients/{id}/photos` (multipart form-data, champ `photo`, optionnel `caption`)

## Migrations

Appliquer les migrations pour créer les tables mobiles:

```bash
php bin/console doctrine:migrations:migrate
```

Migration ajoutée: `Version20260313183000`.

## Stockage photos

Les photos sont stockées dans `public/uploads/patient-photos`.
