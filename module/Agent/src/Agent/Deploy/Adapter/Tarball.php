<?php
/**
 * Continuous Php (http://continuousphp.com/)
 *
 * @author Simon Olbregts
 * @link      http://github.com/continuousphp/deploy-agent for the canonical source repository
 * @copyright Copyright (c) 2014 Continuous Php (http://continuousphp.com/)
 * @license   New BSD License
 *
 */

namespace Agent\Deploy\Adapter;

use Agent\Service\AgentLogger;
use Agent\Service\FileSystem;
use Zend\Http\Client;

class Tarball
{
    /** @var string folder */
    private $folder;
    /** @var string Gz filename */
    private $gzFileName = 'tarball.tar.gz';
    /** @var string Tar filename */
    private $tarFileName = 'tarball.tar';

    /**
     * Constructor
     *
     * @param $destination
     */
    function __construct($destination)
    {
        $this->folder = $destination;
    }

    /**
     * Download and save tar archive
     *
     * @param $tarUrl
     */
    public function downloadArchive($tarUrl)
    {
        $stream = $this->streamFromUrl($tarUrl);
        if ($stream instanceof \Zend\Http\Response\Stream)
            $this->createFromResponseStream($stream, $this->getFolder());
    }

    /**
     * Get stream from http url tarball
     *
     * @param $tarUrl
     * @return \Zend\Http\Response
     */
    public function streamFromUrl($tarUrl)
    {
        AgentLogger::info('Connect to continuous php server');
        $client = new Client($tarUrl, array(
            'sslverifypeer' => null,
            'sslallowselfsigned' => null,
        ));
        $client->setStream();
        $stream = $client->send();
        AgentLogger::info('Connect to continuous php server [done]');
        return $stream;
    }

    /**
     * Copy a stream in destination folder
     *
     * @param \Zend\Http\Response\Stream $stream
     */
    public function createFromResponseStream(\Zend\Http\Response\Stream $stream)
    {
        AgentLogger::info('Downloading tarball');
        FileSystem::mkdirp($this->folder, 0777, true);
        $tarball = $this->folder . $this->getGzFileName();
        copy($stream->getStreamName(), $tarball);
        $fp = fopen($tarball, 'w');
        stream_copy_to_stream($stream->getStream(), $fp);
        fclose($fp);
        AgentLogger::info('Downloading tarball [done]');
    }

    /**
     * Extract tarball in destination path
     *
     * @param null $destinationPath
     * @return bool
     */
    public function extract($destinationPath = null)
    {
        AgentLogger::info('Extraction');
        if (is_null($destinationPath))
            $destinationPath = $this->getFolder();

        $tarball = $this->getFolder() . $this->getGzFileName();
        $done = false;
        if (is_file($tarball)) {
            $tar = new \Archive_Tar($tarball);
            $done = $tar->extract($destinationPath, true);
            AgentLogger::info('Extraction [done]');
        }else{
            AgentLogger::error($tarball.' is not a path to the file');
        }
        return $done;
    }

    /**
     * Clean temporary tar and gzip files
     */
    public function cleanTemporaryFile()
    {
        AgentLogger::info('Delete temporary files');
        $tarball = $this->getFolder() . $this->getGzFileName();
        if (is_file($tarball))
            unlink($tarball);
        AgentLogger::info('Delete temporary files [done]');
    }

    /**
     * @param string $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param string $gzFileName
     */
    public function setGzFileName($gzFileName)
    {
        $this->gzFileName = $gzFileName;
    }

    /**
     * @return string
     */
    public function getGzFileName()
    {
        return $this->gzFileName;
    }

    /**
     * @param string $tarFileName
     */
    public function setTarFileName($tarFileName)
    {
        $this->tarFileName = $tarFileName;
    }

    /**
     * @return string
     */
    public function getTarFileName()
    {
        return $this->tarFileName;
    }

} 