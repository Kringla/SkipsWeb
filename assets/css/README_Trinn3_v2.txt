SkipsWeb Trinn #3 assets (v2)

Denne versjonen:
- Bevarer hero-bilder/farger med lys overlay
- Stor "SkipsWeb" kun på landingssiden via body-klassen .home
- Meny-lenker ser ut som faner (ikke knapper), også hvis de har class="btn"

Bruk:
1) Ta backup av eksisterende /assets/app.css
2) Erstatt med app.css fra denne pakken
3) Hard-refresh (Ctrl+F5)

For at stor logo skal vises på landingssiden:
- Sett $page_class = 'home'; i index.php FØR du inkluderer header.php (se instruks i chatten).
