const TYPE = {

    temperatura: "Temperatura",
    velocita_vento: "Velocità Vento",
    direzione_vento: "Direzione Vento",
    umidita_relativa: "Umidità Relativa",
    radiazione_globale: "Radiazione Globale",
    precipitazione: "Precipitazione",
    altezza_neve: "Altezza Neve",
    ammoniaca: "Ammoniaca",
    arsenico: "Arsenico",
    benzene: "Benzene",
    benzo_pirene: "Benzo(a)pirene",
    biossido_azoto: "Biossido di Azoto",
    biossido_zolfo: "Biossido di Zolfo",
    black_carbon: "BlackCarbon",
    cadmio: "Cadmio",
    livello_idrometrico: "Livello Idrometrico",
    monossido_carbonio: "Monossido di Carbonio",
    nikel: "Nikel",
    ossidi_azoto: "Ossidi di Azoto",
    ozono: "Ozono",
    particelle_sospese_PM: "Particelle sospese PM2.5",//dismesso
    particolare_tot_sospeso:"Particolato Totale Sospeso",
    piombo: "Piombo",
    pm10: "PM10",
    pm10_sm2005: "PM10 (SM2005)",//dismesso


};

class Sensor_Type {}
Sensor_Type.TYPE = TYPE;

module.exports = Sensor_Type;
