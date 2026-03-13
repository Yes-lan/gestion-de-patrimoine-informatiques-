# Instructions pour créer les infirmières de test

## Étape 1: Créer la table infirmiere dans la base de données

Ouvrez PowerShell et exécutez ces commandes **dans l'ordre** :

```powershell
cd "c:\Users\npichon\Desktop\feut\Web"

# Créer la table si elle n'existe pas
docker compose exec php php bin/console doctrine:schema:update --force

# Vérifier que la table est créée
docker compose exec php php bin/console dbal:run-sql "SHOW TABLES LIKE 'infirmiere'"
```

## Étape 2: Insérer les données de test

```powershell
# Exécuter la commande de seed
docker compose exec php php bin/console app:seed:infirmieres-test
```

Cette commande va créer 5 infirmières:
- Claire Martin (claire.martin.infirmiere@test.local)
- Sophie Bernard (sophie.bernard.infirmiere@test.local)
- Nadia Dubois (nadia.dubois.infirmiere@test.local)
- Lea Roux (lea.roux.infirmiere@test.local)
- Camille Petit (camille.petit.infirmiere@test.local)

**Mot de passe pour toutes:** Test1234!

## Alternative: Insertion SQL directe

Si la commande Symfony ne fonctionne pas, utilisez le fichier SQL:

```powershell
cd "c:\Users\npichon\Desktop\feut\Web"
docker compose exec -T database mysql -uapp -papp app < seed_infirmieres.sql
```

## Vérification

Pour vérifier que les infirmières sont bien créées:

```powershell
docker compose exec php php bin/console dbal:run-sql "SELECT id, nom, prenom, email FROM infirmiere"
```

Ou allez sur http://localhost/admin-pannel puis cliquez sur "CRUD Infirmières"
