# Timerr

**Autors:** Artjoms Dvils, DP 3-4

Timerr ir Laravel tīmekļa lietotne pakalpojumu apmaiņai ar laika kredītiem. Platformā lietotāji var publicēt darbus, pieteikties citu lietotāju darbiem, iesniegt izpildes pierādījumus, sarakstīties, atstāt atsauksmes un risināt strīdus ar administratora iesaisti. Projekts ir konteinerizēts ar Docker.

## Funkcionalitāte

- Lietotāju reģistrācija, pieslēgšanās, profils, profila attēli un konta bloķēšanas pārbaude.
- Darba sludinājumu izveide, labošana, dzēšana, kategorijas, attēli un kredītu rezervēšana.
- Darba pieteikumu saņemšana, pabeigšana, failu pielikumi, apstiprināšana un noraidīšana.
- Privātās sarunas starp lietotājiem ar vienu pielikumu katram ziņojumam.
- Atsauksmes pēc apstiprināta darba un publisks cilvēku katalogs.
- Strīdu iesniegšana, iesaldēšana un administratora lēmumu piemērošana.
- Administratora panelis ar statistiku, lietotāju pārvaldību, kredītu korekcijām un kontaktziņojumiem.
- Darījumu vēstures eksports PDF, CSV un XLSX formātā.

## Prasības

- Docker Desktop vai Docker Engine ar Docker Compose.
- Git.

## Instalēšana ar Docker

```bash
git clone https://github.com/rinnetamine/timerr.lat.git
cd timerr.lat
docker compose up --build
```

Pēc palaišanas lietotne būs pieejama:

```text
http://localhost:8000
```