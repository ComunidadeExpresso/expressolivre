expressolivre
=============

Repositório oficial do Projeto Expresso Livre.

Expresso versão 2.5.2
=====================
Para esta versão, apenas a seguinte distribuição está 100% homologada e compatível com o Expresso:
 * Ubuntu LTS Precise Pangolin (12.04.1);

Distribuições parcialmente homologadas:
 * Centos 6 (6.4);
 * Debian Squeeze (6.0.6); 

Estas distribuições, e até outras, podem ser utilizadas e são compatíveis com o Expresso Livre. Porém, não possuem em seus repositórios oficiais os pacotes de software nas versões mínimas exigidas, principalmente o Cyrus-IMAP e PHP. Neste caso, será necessário utilizar um repositório externo, de terceiros ou mesmo uma compilação/geração manual dos pacotes de software.

Requisitos mínimos para qualquer ambiente:
 * PHP > 5.3.3
 * Cyrus > 2.4.0
 * Postgres > 9.0 

Ambiente recomendado:
 * PHP 5.3.23
 * Postgres 9.1.13
 * Cyrus 2.4.13
 * Java 1.6.x (1) 

(1) Em virtude dos esforços empreendidos nas melhorias de performance, especialmente dos módulos ExpressoMail e ExpressoCalendar, é recomendável ter instalado o Java no servidor. O componente FileDuck foi incluído no Expresso, que faz o controle do cache de arquivos javascript e css. Além disto, os arquivos javascript e css podem ser minificados (remoção dos caracteres desnecessários sem alteração das funcionalidades), de forma que possam ser carregados mais rapidamente. 

Para mais informações sobre o processo de instalação acesse o link: http://trac.expressolivre.org/wiki/versoes/25

