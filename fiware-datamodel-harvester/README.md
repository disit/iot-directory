# Fiware Smart data models per Snap4City

#### Overview
Uno [Smart Data Model](https://www.fiware.org/smart-data-models/) è un file `schema.json` che contiene al suo interno definizioni per attributi. 

Queste informazioni sono fondamentali per riuscire a creare un **messaggio**, che un dispositivo
[IoT](https://it.wikipedia.org/wiki/Internet_delle_cose) invia al server (ovvero un [Orion Broker](https://fiware-orion.readthedocs.io/en/master/)). Il messaggio si chiama "**Payload**", e contiene
tutte le informazioni sullo stato del dispositivo, oltre che le letture da eventuali sensori. 
All'atto pratico, *istanziando* un file `schema.json` otteniamo un messaggio, ovvero un payload.

Ognuno di questi modelli, è indicizzato dalla tupla:
> (_Dominio_, _Sottodominio_, _Modello_, _Versione_)

Gli scopi di queste classi, orchestrate dal file `main.py`, sono molteplici, e sono:
 1. Scaricamento degli Smart Data Models (SDM) dalla [repository generica di github](https://github.com/smart-data-models/)
 2. Interpretazione degli `schema.json`, ovvero del file che **definisce** uno SDM, con modifica automatica nel caso di piccoli errori di battitura
 3. Estrazione degli attributi dallo `schema.json`, mascherando gli stessi per essere potenzialmente compatibili con il sistema [Snap4City](https://www.snap4city.org/)
 4. Salvataggio nel database (con DBMS [MySQL](https://www.mysql.com/it/)) dei contenuti dei file `schema.json`, oltre che degli attributi estratti, dei **log** generati dall'estrazione e infine dal **timestamp**
 5. Scaricamento di Payloads indicando il link. Possibile anche scaricare da sistemi [Multitenancy](https://fiware-orion.readthedocs.io/en/master/user/multitenancy/) modificando l'header della richiesta
 6. Validazione dei messaggi rispetto ai rispettivi *modelli*, cioè la verifica di conformità del Payload con lo schema che **dovrebbe** averlo istanziato
 7. Generazione aitomatica di un sistema di regole **model-based**
 
 
#### How to