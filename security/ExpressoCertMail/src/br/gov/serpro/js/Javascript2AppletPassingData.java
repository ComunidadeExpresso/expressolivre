package br.gov.serpro.js;

import java.util.HashMap;
import java.util.Map;

public class Javascript2AppletPassingData {

	private boolean locked = true;
	private String data = null;
    private Map<String, String> mapData = null;

	public synchronized String getData() throws InterruptedException{
		if (isLocked()){
			data = null;
            wait();
		}
		lock();
		//notifyAll();
		return this.data;
	}

	public synchronized Map<String, String> getMap() throws InterruptedException{
		if (isLocked()){
			mapData = null;
            wait();
		}
		lock();
		//notifyAll();
		return this.mapData;
	}

	public synchronized void setData(String data){
		//flag = true;
		this.data = data;
		unlock();
	}

    public synchronized void setData(String operation, String id, String body){
		//flag = true;
        if (this.mapData == null) {
            this.mapData = new HashMap<String, String>();
        }
		this.mapData.put("operation", operation);
        this.mapData.put("ID", id);
        this.mapData.put("body", body);
		unlock();
	}

    public synchronized void setData(String operation, String id, String body, String folder){
        this.mapData = new HashMap<String, String>();
        this.mapData.put("folder", folder);
        this.setData(operation, id, body);
    }

	public synchronized void unlock(){
		locked = false;
		notifyAll();
	}

	private synchronized void lock(){
		locked = true;
	}

	public synchronized boolean isLocked(){
		return locked;
	}
}
