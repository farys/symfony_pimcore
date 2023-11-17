# Pimcore airporter

### Follow these steps

1.Download the contents of this repository and run the following command in the location where the airporter directory is to be created, containing the contents of this project:
```bash
git clone https://github.com/farys/airporter airporter
```
2.Navigate to the airporter project directory
```bash
cd airporter
```

3. Start the database (as a separate service)
```bash
docker-compose up -d db
```

4.Load the content of the database backup
```bash
cat init.sql | docker-compose exec -T db mysql -uroot -pRot192. pimcore
```

5. Run the entire project with additional services
```bash
docker-compose up -d
```
6. Install dependencies
```bash
docker-compose exec php composer install
#docker-compose exec php bin/console pimcore:bundle:install PimcoreDataHubBundle
#docker-compose exec php bin/console pimcore:bundle:install PimcoreDataImporterBundle
```

7. Clear cache
```bash
docker-compose exec php bin/console cache:clear
docker-compose exec php bin/console pimcore:cache:clear
```

8. Log in to the administration panel
`login: pimcore`
`haslo: pimcore`

### Additional Information
1. To initiate the import of the task list, execute the command or set it up as a cron job
```bash
docker-compose exec php bin/console app:import-todos-list
```
2. To run pimcore's parallel tasks
```bash
docker-compose exec php bin/console datahub:data-importer:process-queue-parallel --processes=5
```
3. To run pimcore's sequential tasks
```bash
docker-compose exec php bin/console datahub:data-importer:process-queue-sequential
```
4. To uninstall services installed for the project, execute
```bash
docker-compose rm
```

5. Example CSV files in the /uploads directory.

### Prerequisits

* Your user must be allowed to run docker commands (directly or via sudo).
* You must have docker-compose installed.
* Your user must be allowed to change file permissions (directly or via sudo).
