package cn.laysoft.gocheck;

import java.util.*;
import java.util.Scanner;

public class GoCheck {
	public static void main(String[] args) {
		/*for (int i = 0; i < args.length; i++) {
			int width = 0;
			int height = 2;
			width = Integer.parseInt(args[i]);
			new GoCheck(width, height).run();
		}*/

		Scanner scanner = new Scanner(System.in);
		int width = scanner.nextInt();
		int height = 2;
		while(width > 0) {
			new GoCheck(width, height).run();
			width = scanner.nextInt();
		}
	}


	private int width = 0;
	private int height = 0;
	private ArrayList<ArrayList<Boolean>> flag = new ArrayList<ArrayList<Boolean>>();
	private ArrayList<ArrayList<ArrayList<Arrow>>> chance = new ArrayList<ArrayList<ArrayList<Arrow>>>();
	private ArrayList<ArrayList<Point>> result = new ArrayList<ArrayList<Point>>();

	public GoCheck(int size) {
		this(size, size);
	}

	public GoCheck(int width, int height) {
		this.width = width;
		this.height = height;
		this.init();
	}
	private void init() {
		for (int i = 0; i < this.height; i++) {
			ArrayList<Boolean> f = new ArrayList<Boolean>();
			ArrayList<ArrayList<Arrow>> a = new ArrayList<ArrayList<Arrow>>();
			for (int j = 0; j < this.width; j++) {
				f.add(false);
				int minRow = 0;
				int minCol = 0;
				int maxRow = this.height - 1;
				int maxCol = this.width - 1;
				ArrayList<Arrow> b = new ArrayList<Arrow>();
				if(i != minRow && j != minCol) {
					b.add(Arrow.UP_LEFT);
				}
				if(i != minRow) {
					b.add(Arrow.UP_CENTER);
				}
				if(i != minRow && j != maxCol) {
					b.add(Arrow.UP_RIGHT);
				}
				if(j != minCol) {
					b.add(Arrow.MIDDLE_LEFT);
				}
				if(j != maxCol) {
					b.add(Arrow.MIDDLE_RIGHT);
				}
				if(i != maxRow && j != minCol) {
					b.add(Arrow.BOTTOM_LEFT);
				}
				if(i != maxRow) {
					b.add(Arrow.BOTTOM_CENTER);
				}
				if(i != maxRow && j != maxCol) {
					b.add(Arrow.BOTTOM_RIGHT);
				}
				a.add(b);
			}
			this.chance.add(a);
			this.flag.add(f);
		}
	}

	//@SuppressWarnings("unchecked")
	public void run() {
		//boolean has = true;
		//long total = this.width * this.height;
		Counter counter = new Counter();
		
		for(int i = 0;i < this.width; i++) {
			for(int j = 0; j < this.height; j++) {
				Cursor cursor = new Cursor(i, j);
				ArrayList<Point> finished = new ArrayList<Point>();
				ArrayList<ArrayList<Boolean>> flag = this.cloneFlag(this.flag);
				ArrayList<Arrow> chance = this.chance.get(j).get(i);
				flag.get(j).set(i, true);
				finished.add(cursor.toPoint());
				//System.out.println("Start:" + i + "," + j);
				if(finished.size() == this.width * this.height) {
					counter.increase();
					//System.out.println("----finished:" + finished);
					this.result.add(finished);
				} else {
					for (int k = 0; k < chance.size(); k++) {
						Arrow arrow = chance.get(k);
						Cursor newcursor = cursor.move(arrow);
						if(this.valid(newcursor, counter, finished, flag)) {
							ArrayList<ArrayList<Boolean>> newflag = this.cloneFlag(flag);//.clone();
							ArrayList<Point> newFinished = this.cloneFinished(finished);//.clone();
							newflag.get(newcursor.getY()).set(newcursor.getX(), true);
							newFinished.add(newcursor.toPoint());
							this.recur(newcursor, counter, newFinished, newflag);
						}
					}
				}
			}
		}
		System.out.println("counter:" + counter.get());
	}
	
	private void recur(Cursor cursor, Counter counter, ArrayList<Point> finished, ArrayList<ArrayList<Boolean>> flag) {
		if(finished.size() == this.width * this.height) {
			counter.increase();
			//System.out.println("----finished:" + finished);
			this.result.add(finished);
		} else {
			int x = cursor.getX();
			int y = cursor.getY();
			ArrayList<Arrow> chance = this.chance.get(y).get(x);
			for (int i = 0; i < chance.size(); i++) {
				Arrow arrow = chance.get(i);
				Cursor newcursor = cursor.move(arrow);
				//System.out.println("----flag:" + flag);
				//ArrayList<Point> newFinished = (ArrayList<Point>) finished.clone();
				//ArrayList<ArrayList<Boolean>> newflag = (ArrayList<ArrayList<Boolean>>) flag.clone();
				//System.out.println(flag);
				if(this.valid(newcursor, counter, finished, flag)) {
					ArrayList<ArrayList<Boolean>> newflag = this.cloneFlag(flag);
					ArrayList<Point> newFinished = this.cloneFinished(finished);
					newflag.get(newcursor.getY()).set(newcursor.getX(), true);
					newFinished.add(newcursor.toPoint());
					this.recur(newcursor, counter, newFinished, newflag);
				}
			}
		}
	}

	private ArrayList<Point> cloneFinished(ArrayList<Point> arr) {
		ArrayList<Point> newArr = new ArrayList<Point>();
		for (Point p : arr) {
			newArr.add(new Point(p.getX(), p.getY()));
		}
		return newArr;
	}
	private ArrayList<Boolean> cloneFlagBoolean(ArrayList<Boolean> arr) {
		ArrayList<Boolean> newArr = new ArrayList<Boolean>();
		for (Boolean b : arr) {
			newArr.add(b);
		}
		return newArr;
	}
	private ArrayList<ArrayList<Boolean>> cloneFlag(ArrayList<ArrayList<Boolean>> finished) {
		ArrayList<ArrayList<Boolean>> newArr = new ArrayList<ArrayList<Boolean>>();
		for (ArrayList<Boolean> object : finished) {
			newArr.add(this.cloneFlagBoolean(object));
		}
		return newArr;
	}
	
	public boolean valid(Point point, Counter counter, ArrayList<Point> finished, ArrayList<ArrayList<Boolean>> flag) {
		int x = point.getX();
		int y = point.getY();
		//System.out.println(flag);
		//System.out.println(finished);
		if(x >= this.width || x < 0) {
			return false;
		}
		if(y >= this.height || y < 0) {
			return false;
		}
		if(flag.get(y).get(x)) {
			return false;
		}
		//System.out.println("--------isTrue:---------");
		return true;
	}
}
enum Arrow {
	UP_LEFT(-1, -1), UP_CENTER(0, -1), UP_RIGHT(1, -1), MIDDLE_LEFT(-1, 0), MIDDLE_RIGHT(1, 0), BOTTOM_LEFT(-1, 1), BOTTOM_CENTER(0, 1), BOTTOM_RIGHT(1, 1);
	private int x;
	private int y;
	public int getX() {
		return x;
	}
	public int getY() {
		return y;
	}
	// 构造方法
    private Arrow(int x, int y) {
        this.x = x;
        this.y = y;
    }
    public String toString() {
		return this.x + ", " + this.y;
    }
}
class Counter {
	private int num = 0;
	public Counter() {
	}
	public void increase() {
		this.num++;
	}
	public void decrease() {
		this.num--;
	}
	public int get() {
		return this.num;
	}
}
class Cursor extends Point {
	public Cursor(int x, int y) {
		super(x, y);
	}
	public Cursor move(Arrow arrow) {
		//this.x += arrow.getX();
		//this.y += arrow.getY();
		return new Cursor(this.x + arrow.getX(), this.y + arrow.getY());
	}
	public Cursor unmove(Arrow arrow) {
		this.x -= arrow.getX();
		this.y -= arrow.getY();
		return this;
	}
	public Point toPoint() {
		return new Point(this.x, this.y);
	}
	public Cursor toCursor() {
		return new Cursor(this.x, this.y);
	}
}
class Point {
	protected int x;
	protected int y;
	public boolean equals(Point obj) {
		if(this.x == obj.x && this.y == obj.y) {
			return true;
		}
		return false;
	}
	public Point(int x, int y) {
		this.x = x;
		this.y = y;
	}
	public int getX() {
		return x;
	}
	public int getY() {
		return y;
	}
	public void setX(int x) {
		this.x = x;
	}
	public void setY(int y) {
		this.y = y;
	}
	public void setXY(int x, int y) {
		this.x = x;
		this.y = y;
	}
	public int sum() {
		return this.x + this.y;
	}
	public String toString() {
		return this.x + "," + this.y;
	}
}
