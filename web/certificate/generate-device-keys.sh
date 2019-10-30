export IOTDIR_CA_CRL_URI=URI:http://dashboard/example_ca.crl
export IOTDIR_CA_PASSWORD=pass:<ca_password>

cd $3
export IOTDIR_CLIENT_COMMON_NAME=$1

openssl req -config client.cnf -new -newkey rsa:1024 -keyout private/$2-key.pem -out certreqs/$2-csr.pem -nodes
openssl ca -batch -config client.cnf -days 365 -extensions v3_ca_has_san -out certsdb/$2-crt.pem -keyfile private/ca-key.pem -passin $IOTDIR_CA_PASSWORD -cert ca-crt.pem -infiles certreqs/$2-csr.pem 
openssl x509 -pubkey -noout -in certsdb/$2-crt.pem  > public/$2-pubkey.pem

