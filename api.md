## Table of contents

- [\Plinker\Core\Server](#class-plinkercoreserver)
- [\Plinker\Core\Client](#class-plinkercoreclient)

<hr />

### Class: \Plinker\Core\Server

> Plinker\Core\Server

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>Class construct</em> |
| public | <strong>listen()</strong> : <em>void</em><br /><em>Listen method <code> $server->listen(); </code></em> |

<hr />

### Class: \Plinker\Core\Client

> Plinker\Core\Client

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__call(</strong><em>string</em> <strong>$action</strong>, <em>array</em> <strong>$params</strong>)</strong> : <em>array</em><br /><em>Magic caller method, which calls component</em> |
| public | <strong>__construct(</strong><em>string</em> <strong>$server</strong>, <em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>Class construct</em> |
| public | <strong>__get(</strong><em>string</em> <strong>$component</strong>)</strong> : <em>object</em><br /><em>Magic getter method, which sets component</em> |

