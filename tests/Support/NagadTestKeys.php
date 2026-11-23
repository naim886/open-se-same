<?php

namespace Tests\Support;

/**
 * Static RSA key pairs used to exercise the Nagad encryption/signature flow
 * without depending on the OpenSSL CLI or a system openssl.cnf.
 */
trait NagadTestKeys
{
    protected function merchantTestPrivateKey(): string
    {
        return <<<'PEM'
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAs7rfVub8c6Bno0tWEuF2vS0bmRb5bJgGrxVispVGv3k51be/
EUA9UW5Gwso+HeIRlepFR/4Lk4cgrO9FslZnKpkCsVV5kuuDJp/asWbkTpPMP0pf
RJZ5/3+fOcwzfgttBqKy7ChkcNzSKmYuFzhofp3WQKqPuZi/5ovwxyrwNCqZ2O4v
toeg6m+4KKjtdpOQjlCCSwe0bzWW5QoLW4mUvsVGiB/qcSTVgiLBtf9oYP+GPQGk
AfwzSezgpjn5rgQwmTftQ6OpA2COsE4YC5dboA5PnpFLKkVjUZlkR60YzcKVRzGV
lT5agcxmzNuVTP2k7Z7IKAIczgUKnkid1RDJNwIDAQABAoIBAHQP3APRW/sK/YIM
t0OwBy54v5anqGBA+Cf+BmNOVIZ3MSra4m6wbih0rwtA8MX5Y+PMifrIig81tpsW
VIqqkyqF7qA3ky+WGHZLyjy/NfWsKbxBOa4gAh5E+rynKiVeGYSsciBgoSPk5W7t
1xu1mH0TTfUqb2+pvVqSyeTDzKPSPBhoFxRfGxByC6yTkJvBZKU36Ad7uOExUX4r
oj44PJ5NAb0kEdEJuom4vFyrSVNlfWW3eCo0QxwQMyUdONA8grglySNdzzG/HDWM
rJ447pwT9WOI6Z6J2PVSXiCGUAswaJz2lrxLeRaLPNJQWnfH7NBBgewc3SHJFAWo
ZbaNvHkCgYEA74IrTsTEatK+F1G49jz3GR7+YqTJ2qC7TRvDS64zTPHceFS/37n8
u8zVnj5nH8bjo7hZgWWRoc4o+kHcF8tTEfSn07rU8cGk2BYatKjAWd33VPsQDOiv
uZaZFhh+0lrY4CwqQPgjcZLsH4kln5pzCNYhwJHbDQn7FdZ+npvd//0CgYEAwBr7
3Qo8VwtRIkEbqy5ZEneZOmuATx2OHmfmEKHAEpDgEgoV90qcJzaBpkMrlT6sWsDH
VjFe1+qG1wzE0yW5mcW9Fyyjm3PBttQGHc+gv5BW3Bs7I+wRJoU8U7UrbcCV4aqW
psWh179uh9ituOqL/D1XVMYu2F1z2F6t0YYDEkMCgYA5kubqNm6o5yP+4ww9VQZp
KYQpHkfPoFel/4iAxs1aL8G5w1WkaEcStfib45BSmTLbhlMtwcaySKadEfvgVGYR
qOg3A3Bw9QW1VArzZqVmdO8hpICFOeIUENmX8Cd8VY5QxQ0bNQsAIDtWkVdT+IKy
mxbd/bHUfpO2+irR4dIJ2QKBgBi74K3HAZnbdSjJ5TwXDZTZHTtuV+jXz1KO4gpG
KSq6yH7KGaXrT0ulMvArOdNPbHWK2J96X9HA+nd5x2XAN7NayEvRjYcYUlqt1dPE
xdekR+qy/txTKqohAXFtpBfPcfzKL/ds/rd+iEapWUSzvi5N346kradh8OZs2khd
zsttAoGBAK0I2/iHk4I/nleQUwAeJXguyQQXB3e61rhoxVxVMHTv3dG6WAS1JSHb
a5T12psxeGhK2eOd4v9WfsxiKeSHTl41D4eTxtMTromy4zEDaFto2mbkJZx769ka
5PuezHWao1TPBevQQwkYZmq/Ps1dMcuMOoACJ8VfeenNR8+09yRB
-----END RSA PRIVATE KEY-----
PEM;
    }

    protected function merchantTestPublicKey(): string
    {
        return <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs7rfVub8c6Bno0tWEuF2
vS0bmRb5bJgGrxVispVGv3k51be/EUA9UW5Gwso+HeIRlepFR/4Lk4cgrO9FslZn
KpkCsVV5kuuDJp/asWbkTpPMP0pfRJZ5/3+fOcwzfgttBqKy7ChkcNzSKmYuFzho
fp3WQKqPuZi/5ovwxyrwNCqZ2O4vtoeg6m+4KKjtdpOQjlCCSwe0bzWW5QoLW4mU
vsVGiB/qcSTVgiLBtf9oYP+GPQGkAfwzSezgpjn5rgQwmTftQ6OpA2COsE4YC5db
oA5PnpFLKkVjUZlkR60YzcKVRzGVlT5agcxmzNuVTP2k7Z7IKAIczgUKnkid1RDJ
NwIDAQAB
-----END PUBLIC KEY-----
PEM;
    }

    protected function nagadTestPrivateKey(): string
    {
        return <<<'PEM'
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA6S2Rmttvmf8HcWAnzDmbCMzhE7RuqisIItUxyl3/k3FB+e5N
DvhO6oPdb9w4bPF8q0r34hR80HFMWj/fh0QiED4JR75Jx4Pjjpp4CcPQDqzqUSny
M9TALF874VcFempToHtlN763rSJgWRa04Aomga50j9wYZy2szjNBztOD/pVsn0Fr
AIVqN0dL5/LqVh76lNDaQeJYsez2DDyOA/Qpb3KrNQfgnEppWiyWwgU9N34s3Xlw
m3ch3oOe1dos45qVEF8KDebTm8sfJNYHalo6jztQENSKt1pvMAcjHfPcAmqS+hX3
IuEdymwyt9dTiR5yKhTA6K+6CHIMpbzQQx+/yQIDAQABAoIBAQDddFvO+Q0a8mz9
LlddzHADgaFZ94kKsGe/vT7WdUOfoncIiVxCFW3NqIMME9i7DvZaMI8P9mk8Bf5/
0B3Y98pqJqDltEGZ7RaXfvj2PTRCW9MGVpIiy7Hf8mqKbv1S79yQk+DjeowzMGzU
743FIZ7Elw04+81pYKW4CFI9vgCkLTGUMHpVJ8C+luMdimhAjeCDwz2nlC0G0J6r
5J6zthORr1csGjyIhM7Zt3G1RRyc3KUm+jT+FccFU8EeYVHCnZfwurM64sfOzWI8
tbL2/yGmEGQZ1pdt8I27PYsC4KPTNY+btUvAJhyEV09zqeaifJse7gg5qtaDTAvE
QDGEKAEBAoGBAPm7jbFkcjvGct8R9dUFUVgZXMLQeno8WM/nbUzlvbMc7KM/hsJI
5pmbTOGPKuVW1RmH6jDn4jsoly7eQOj3owedu2/a6rx30qii62itxZ4fVkBhuUYI
IYywAmh3mOH6TnbqWxJc0T9FBSAoy6T8voOBICWm7Hhf88+lE9cpvS6xAoGBAO8H
qFBAzKLR9qE/+2XV6CzrGOBXl+f0yU6njky9bEVpa77hw7aTra7lrsYiCSIMCfos
LWrrYiR7p+vqVBODI1hhfljezNzBH2AqV9yzLYdqM8ZXJthhG8yUKJn5fx5f5Doy
NaTC9hxraMjK6JjON+ojOscA9mWBn4hXyATNoliZAoGBALP5BC45U1HcvGAiU2XH
RU4WSRFkqT96Sg7am5x1XPa9+5PSbk2JuBYvI4LSaHmK0Fxr5MyF8DXuFLYEGgdK
n8UXWUNNIynbuIcrkjeINAi2ULRpONpNddAr8DBVMGp6tx1f224qUeok1YLNo6Ut
OCcRYZeA6p39mHmmX9wKCnqhAoGAR/EpEoZ+UN8a+Ot18e+Qwrw7r4INoW3GjRrX
NHeqt7WEDV6L9k66brKIiO/DifDPq/FR9iVEU7C4GMRda8YDhF1LltnwFN03YU09
RprTMDehv6eJdIS9xjQtiDJUtUKtQWrNRHq9ck5NqmmP2jXvFFRoI29Buclqx7GL
7Q4rN7kCgYBOu+txlU10ae+DwiXpWI5oMICzmrn67QFunDLzGBvKSxFcLcEVQbVM
vRaHFX9JwTLsWyKhh75G8UgJrVvIr4hekmEQt+Z5KhWmxYou0zcGdhDI0t9CQBt0
qwiu4V7QPw+q4sc/jwzOzkLpRmMQmVRbwXHZTzojGziDh3vbPuhsuA==
-----END RSA PRIVATE KEY-----
PEM;
    }

    protected function nagadTestPublicKey(): string
    {
        return <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA6S2Rmttvmf8HcWAnzDmb
CMzhE7RuqisIItUxyl3/k3FB+e5NDvhO6oPdb9w4bPF8q0r34hR80HFMWj/fh0Qi
ED4JR75Jx4Pjjpp4CcPQDqzqUSnyM9TALF874VcFempToHtlN763rSJgWRa04Aom
ga50j9wYZy2szjNBztOD/pVsn0FrAIVqN0dL5/LqVh76lNDaQeJYsez2DDyOA/Qp
b3KrNQfgnEppWiyWwgU9N34s3Xlwm3ch3oOe1dos45qVEF8KDebTm8sfJNYHalo6
jztQENSKt1pvMAcjHfPcAmqS+hX3IuEdymwyt9dTiR5yKhTA6K+6CHIMpbzQQx+/
yQIDAQAB
-----END PUBLIC KEY-----
PEM;
    }
}
