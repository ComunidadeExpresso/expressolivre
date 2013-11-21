# -*- coding: utf-8 -*-

# Lista com as urls das CRLs, e onde colocar(path) os arquivos obtidos ....
CRL_urls = []
Confs = {}

#Confs['dirtemp']         # path para a pasta temp para conter arquivos auxiliares....
#Confs['CAfile']          # Arquivo com cadeia dos certificados das CAs, para verificacao das CRLs.
#Confs['CRLs']            # path para a pasta onde as CRLs sao salvas
#Confs['arquivos_crls']   # path para o arquivo de configuracao contendo urls das crls e paths onde serao baixadas...
#Confs['log']             # Arquivo onde sera grada log de execucao da qtualizacao/verificacao das crls.
                          # Deixe 'log' igual a vazio para ver as msgs de execucao no terminal.......
def ler_arquivo_com_configuracao():
    # Esta funcao le o arquivo com configuracao geral(linguagen php) para tratar certs.
    import os,sys
    BASE = os.path.realpath(__file__).split(os.sep + 'security')[0] # BASE igual a pasta inicial(raiz) do Expresso
    os.chdir(BASE + '/security/classes')
    # Esta funcao le o arquivo com configuracao geral(linguagen php) para tratar certs.
    conf_file = BASE + '/security/classes/Verifica_Certificado_conf.php'
    e = open(conf_file)
    r = e.read()
    aux1 = r.split('\n')
    # primeiro recupera BASE ...
    for linha in aux1:
        linha = linha.strip()
        if linha[0:16] == "$GLOBALS['BASE']":
            Confs['BASE'] = BASE
            break
    # Agora os demais ...
    for linha in aux1:
        linha = linha.strip()
        if linha[0:10] == "$GLOBALS['":
          if linha[0:16] != "$GLOBALS['BASE']":
            aux2 = linha.split(';')
            if aux2[0] != '':
                aux2a = aux2[0].split("'")
                aux3 = aux2[0].split("=")
                Confs[aux2a[1]] = aux3[1].replace(' ','')
    # Finalmente trata as ocorrencias de BASE ...
    for chave in Confs.keys():
        if chave != 'BASE':
            aux = Confs[chave].replace("$GLOBALS['BASE'].",Confs['BASE'])
            Confs[chave] = aux.replace("'",'')
    return

def ler_conf():
    # Esta funcao le o arquivo passado como parametro e gera a lista CRL_urls.
    # O arquivo he esperado no formato:
    # url ( url = aponta onde buscar a crl,  uma por linha.
    ler_arquivo_com_configuracao()
    e = open(Confs['arquivos_crls'])
    r = e.read()
    aux1 = r.split('\n')
    for linha in aux1:
        if linha[0:1] != '#':
            if linha != '':
                # Faz split com ';' para manter compatibilidade com arquivos formato antigo ...
                CRL_urls.append([linha.split(';')[0],Confs['CRLs']])
    return
