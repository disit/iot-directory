export IOTDIR_CA_COUNTRY_NAME=IT
export IOTDIR_CA_STATE=Florence
export IOTDIR_CA_LOCALITY=Florence
export IOTDIR_CA_ORGANIZATION_NAME=Your_CA_organization_name
export IOTDIR_CA_ORGANIZATION_UNIT=Your_CA_organization_unit_name
export IOTDIR_CA_COMMON_NAME=Your_CA_Root_name
export IOTDIR_CA_EMAIL_ADDRESS=your@ca.email
export IOTDIR_CA_CRL_URI=URI:http://dashboard/example_ca.crl
export IOTDIR_CA_PASSWORD=pass:<ca_password>

#remove old installation
rm -r certsdb
rm -r certreqs
rm -r crl
rm -r private
rm -r newcerts
rm -r public
rm -r index.txt*
rm -r serial
rm -r ca-crt.pem
rm -r .rnd

#fresh installation
mkdir certsdb
mkdir certreqs
mkdir crl
mkdir private
mkdir newcerts
mkdir public
chmod 700 private
touch index.txt
chown www-data:www-data private
chown www-data:www-data certreqs
chown www-data:www-data public
chown www-data:www-data certsdb
chown www-data:www-data newcerts

openssl req -config ca.cnf -new -newkey rsa:4096 -keyout private/ca-key.pem -out certreqs/ca-csr.pem -passout $IOTDIR_CA_PASSWORD
openssl ca -batch -create_serial -config ca.cnf -days 9999 -selfsign -extensions v3_ca_has_san -out ca-crt.pem -keyfile private/ca-key.pem -passin $IOTDIR_CA_PASSWORD -cert ca-crt.pem -infiles certreqs/ca-csr.pem
