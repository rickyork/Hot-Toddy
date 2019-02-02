<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

# Simple server class which manage WebSocket protocols
#
# Original author: Sann-Remy Chea <http://srchea.com>
#
# @license This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#   
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# @version 0.1

class hFrameworkServer extends hPlugin {

    private $address;            # The address of the server
    private $port;               # The port for the master socket
    private $master;             # The master socket
    private $sockets;            # The array of sockets (1 socket = 1 client)
    private $clients;            # The array of connected clients

    function hConstructor($arguments)
    {
        # $address = '127.0.0.1', $port = 5001
        
        $this->console("Server starting...");

        if (isset($arguments['address']))
        {
            $this->address = $arguments['address'];
        }
        else
        {
            $this->address = '127.0.0.1';
        }

        if (isset($arguments['port']))
        {
            $this->port = $arguments['port'];
        }
        else
        {
            $this->port = 8080;
        }

        # socket creation
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        if (!is_resource($socket))
        {
            $this->fatal("socket_create() failed: ".socket_strerror(socket_last_error()), __FILE__, __LINE__);
        }

        if (!socket_bind($socket, $this->address, $this->port))
        {
            $this->fatal("socket_bind() failed: ".socket_strerror(socket_last_error()), __FILE__, __LINE__);
        }

        if (!socket_listen($socket, 20))
        {
            $this->fatal("socket_listen() failed: ".socket_strerror(socket_last_error()), __FILE__, __LINE__);
        }

        $this->master = $socket;
        $this->sockets = array($socket);
        $this->console("Server started on {$this->address}:{$this->port}");
    }


    # Create a client object with its associated socket      
    private function connect($socket)
    {
        $this->console("Creating client...");

        $client = new hFrameworkServerClient(uniqid(), $socket);

        $this->clients[] = $client;
        $this->sockets[] = $socket;

        $this->console("Client #{$client->getId()} is successfully created!");
    }

    # Do the handshaking between client and server
    private function handshake($client, $headers)
    {
        $this->console("Getting client WebSocket version...");

        if (preg_match("/Sec-WebSocket-Version: (.*)\r\n/", $headers, $match))
        {
            $version = $match[1];
        }
        else
        {
            $this->console("The client doesn't support WebSocket");
            return false;
        }

        $this->console("Client WebSocket version is {$version}, (required: 13)");

        if ($version == 13)
        {
            # Extract header variables
            
            $this->console("Getting headers...");

            if (preg_match("/GET (.*) HTTP/", $headers, $match))
            {
                $root = $match[1];
            }
            
            if (preg_match("/Host: (.*)\r\n/", $headers, $match))
            {
                $host = $match[1];
            }

            if (preg_match("/Origin: (.*)\r\n/", $headers, $match))
            {
                $origin = $match[1];
            }

            if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match))
            {
                $key = $match[1];
            }

            $this->console("Client headers are:");
            $this->console("\t- Root: ".$root);
            $this->console("\t- Host: ".$host);
            $this->console("\t- Origin: ".$origin);
            $this->console("\t- Sec-WebSocket-Key: ".$key);
            
            $this->console("Generating Sec-WebSocket-Accept key...");

            $acceptKey = $key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

            $acceptKey = base64_encode(sha1($acceptKey, true));

            $upgrade = "HTTP/1.1 101 Switching Protocols\r\n".
                       "Upgrade: websocket\r\n".
                       "Connection: Upgrade\r\n".
                       "Sec-WebSocket-Accept: $acceptKey".
                       "\r\n\r\n";
            
            $this->console("Sending this response to the client #{$client->getId()}:\r\n".$upgrade);
            
            socket_write($client->getSocket(), $upgrade);

            $client->setHandshake(true);
            $this->console("Handshake is successfully done!");
            return true;
        }
        else
        {
            $this->console("WebSocket version 13 required (the client supports version {$version})");
            return false;
        }
    }

    # Disconnect a client and close the connection
    private function disconnect($client)
    {
        $this->console("Disconnecting client #{$client->getId()}");

        $i = array_search($client, $this->clients);
        $j = array_search($client->getSocket(), $this->sockets);
        
        if ($j >= 0)
        {
            array_splice($this->sockets, $j, 1);
            socket_close($client->getSocket());
            $this->console("Socket closed");
        }
        
        if ($i >= 0)
        {
            array_splice($this->clients, $i, 1);
        }

        $this->console("Client #{$client->getId()} disconnected");
    }


    # Get the client associated with the socket
    private function getClientBySocket($socket)
    {
        foreach ($this->clients as $client)
        {
            if ($client->getSocket() == $socket)
            {
                $this->console("Client found");
                return $client;
            }
        }

        return false;
    }
    
    # Do an action
    private function action($client, $action)
    {
        $action = $this->unmask($action);
        $this->console("Performing action: ".$action);
        
        if ($action == 'exit' || $action == 'quit')
        {
            $this->console("Killing a child process");
            posix_kill($client->getPid(), SIGTERM);
            $this->console("Process {$client->getPid()} is killed!");
        }
    }
    
    # Run the server
    public function run()
    {
        $this->console("Start running...");

        while (true)
        {
            $changed_sockets = $this->sockets;
            
            @socket_select($changed_sockets, $write = NULL, $except = NULL, 1);
            
            foreach ($changed_sockets as $socket)
            {
                if ($socket == $this->master)
                {
                    if (($acceptedSocket = socket_accept($this->master)) < 0)
                    {
                        $this->console("Socket error: ".socket_strerror(socket_last_error($acceptedSocket)));
                    }
                    else
                    {
                        $this->connect($acceptedSocket);
                    }
                }
                else
                {
                    $this->console("Finding the socket that associated to the client...");
                    $client = $this->getClientBySocket($socket);
                    
                    if ($client)
                    {
                        $this->console("Receiving data from the client");

                        $bytes = @socket_recv($socket, $data, 2048, MSG_DONTWAIT);

                        if (!$client->getHandshake())
                        {
                            $this->console("Doing the handshake");
                            
                            if ($this->handshake($client, $data))
                            {
                                $this->startProcess($client);
                            }
                        }
                        else if ($bytes === 0)
                        {
                            $this->disconnect($client);
                        }
                        else
                        {
                            // When received data from client
                            $this->action($client, $data);
                        }
                    }
                }
            }
        }
    }
    
    # Start a child process for pushing data  
    private function startProcess($client)
    {
        $this->console("Start a child process");
        
        $pid = pcntl_fork();

        if ($pid == -1)
        {
            $this->fatal('Could not fork.', __FILE__, __LINE__);
        }
        else if ($pid) # process
        { 
            $client->setPid($pid);
        }
        else
        {
            # we are the child (you is?)
            while (true)
            {
                # push something to the client
                $seconds = rand(2, 5);
                $this->send($client, "I am waiting {$seconds} seconds");
                sleep($seconds);
            }
        }
    }

    # Send a text to client
    private function send($client, $text)
    {
        $this->console("Send '{$text}' to client #{$client->getId()}");
        $text = $this->encode($text);

        if (socket_write($client->getSocket(), $text, strlen($text)) === false)
        {
            $this->console("Unable to write to client #{$client->getId()}'s socket");
            $this->disconnect($client);
        }
    }

    # Encode a text for sending to clients via ws://
    private function encode($text)
    {
        // 0x1 text frame (FIN + opcode)
        $b1 = 0x80 | (0x1 & 0x0f);

        $length = strlen($text);
        
        if ($length <= 125)
        {
            $header = pack('CC', $b1, $length);
        }
        else if ($length > 125 && $length < 65536)
        {
            $header = pack('CCS', $b1, 126, $length);
        }
        else if ($length >= 65536)
        {
            $header = pack('CCN', $b1, 127, $length);
        }

        return $header.$text;
    }

    # Unmask a received payload
    private function unmask($payload)
    {
        $length = ord($payload[1]) & 127;

        if ($length == 126)
        {
            $masks = substr($payload, 4, 4);
            $data = substr($payload, 8);
        }
        else if ($length == 127)
        {
            $masks = substr($payload, 10, 4);
            $data = substr($payload, 14);
        }
        else
        {
            $masks = substr($payload, 2, 4);
            $data = substr($payload, 6);
        }

        $text = '';

        for ($i = 0; $i < strlen($data); ++$i)
        {
            $text .= $data[$i] ^ $masks[$i%4];
        }

        return $text;
    }
}

class hFrameworkServerClient {

    private $id;
    private $socket;
    private $handshake;
    private $pid;
    
    function __construct($id, $socket)
    {
        $this->id = $id;
        $this->socket = $socket;
        $this->handshake = false;
        $this->pid = null;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getSocket()
    {
        return $this->socket;
    }
    
    public function getHandshake()
    {
        return $this->handshake;
    }
    
    public function getPid()
    {
        return $this->pid;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setSocket($socket)
    {
        $this->socket = $socket;
    }
    
    public function setHandshake($handshake)
    {
        $this->handshake = $handshake;
    }
    
    public function setPid($pid)
    {
        $this->pid = $pid;
    }
}

?>
